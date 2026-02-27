<?php

namespace App\Http\Controllers;

use App\Models\Wish;
use App\Models\WishTransaction;
use App\Models\Kid;
use App\Models\Transaction;
use App\Services\UrlScraperService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WishController extends Controller
{
    protected $urlScraper;

    public function __construct(UrlScraperService $urlScraper)
    {
        $this->urlScraper = $urlScraper;
    }

    // ============================================
    // KID ROUTES
    // ============================================

    /**
     * Display kid's wish list (tabbed: Current & Redeemed)
     */
    public function index()
    {
        $kid = Auth::guard('kid')->user();

        $currentWishes = $kid->wishes()
            ->whereIn('status', ['saved', 'pending_approval', 'declined'])
            ->orderByRaw("CASE status WHEN 'pending_approval' THEN 0 WHEN 'saved' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'desc')
            ->get();

        $redeemedWishes = $kid->wishes()
            ->where('status', 'purchased')
            ->orderBy('purchased_at', 'desc')
            ->paginate(10);

        return view('wishes.index', compact('kid', 'currentWishes', 'redeemedWishes'));
    }

    /**
     * Store a newly created wish
     */
    public function store(Request $request)
    {
        $kid = Auth::guard('kid')->user();

        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'item_url' => 'nullable|url|max:2000',
            'price' => 'required|numeric|min:0|max:99999.99',
            'reason' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:5120', // 5MB max
            'scraped_image_url' => 'nullable|url',
        ]);

        DB::beginTransaction();

        try {
            // Handle image upload or scraping
            $imagePath = null;

            if ($request->hasFile('image')) {
                // Manual upload takes precedence
                $imagePath = $request->file('image')->store('wish-photos', 'public');
            } elseif ($request->filled('scraped_image_url')) {
                // Download scraped image
                $imagePath = $this->urlScraper->downloadImage($request->scraped_image_url);
            }

            // Create wish
            $wish = $kid->wishes()->create([
                'family_id' => $kid->family_id,
                'item_name' => $validated['item_name'],
                'item_url' => $validated['item_url'],
                'image_path' => $imagePath,
                'price' => $validated['price'],
                'reason' => $validated['reason'],
                'status' => $request->input('action') === 'request' ? 'pending_approval' : 'saved',
                'requested_at' => $request->input('action') === 'request' ? now() : null,
            ]);

            // Create transaction record
            $wish->wishTransactions()->create([
                'kid_id' => $kid->id,
                'family_id' => $kid->family_id,
                'transaction_type' => $request->input('action') === 'request' ? 'requested' : 'created',
                'created_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('kid.dashboard')
                ->with('success', $request->input('action') === 'request'
                    ? 'Wish sent to your parent for approval!'
                    : 'Wish added to your list!')
                ->with('active_tab', 'wishes');

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors(['error' => 'Failed to create wish. Please try again.'])->withInput();
        }
    }

    /**
     * Display the specified wish
     */
    public function show(Wish $wish)
    {
        $kid = Auth::guard('kid')->user();

        // Authorization check
        if ($wish->kid_id !== $kid->id) {
            abort(403, 'Unauthorized');
        }

        $wish->load('wishTransactions');

        return view('wishes.show', compact('wish', 'kid'));
    }

    /**
     * Update the specified wish
     */
    public function update(Request $request, Wish $wish)
    {
        $kid = Auth::guard('kid')->user();

        // Authorization and editability checks
        if ($wish->kid_id !== $kid->id) {
            abort(403, 'Unauthorized');
        }

        if (!$wish->canBeEdited()) {
            return back()->withErrors(['error' => 'This wish cannot be edited.']);
        }

        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'item_url' => 'nullable|url|max:2000',
            'price' => 'required|numeric|min:0|max:99999.99',
            'reason' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:5120',
            'scraped_image_url' => 'nullable|url',
        ]);

        DB::beginTransaction();

        try {
            // Handle image update
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($wish->image_path) {
                    Storage::disk('public')->delete($wish->image_path);
                }
                $wish->image_path = $request->file('image')->store('wish-photos', 'public');
            } elseif ($request->filled('scraped_image_url') && $request->scraped_image_url !== $wish->image_path) {
                // New scraped image
                if ($wish->image_path) {
                    Storage::disk('public')->delete($wish->image_path);
                }
                $wish->image_path = $this->urlScraper->downloadImage($request->scraped_image_url);
            }

            $wish->update([
                'item_name' => $validated['item_name'],
                'item_url' => $validated['item_url'],
                'price' => $validated['price'],
                'reason' => $validated['reason'],
            ]);

            DB::commit();

            return redirect()->route('kid.wishes.show', $wish)
                ->with('success', 'Wish updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors(['error' => 'Failed to update wish. Please try again.'])->withInput();
        }
    }

    /**
     * Remove the specified wish
     */
    public function destroy(Wish $wish)
    {
        $kid = Auth::guard('kid')->user();

        if ($wish->kid_id !== $kid->id) {
            abort(403, 'Unauthorized');
        }

        if (!$wish->canBeEdited()) {
            return back()->withErrors(['error' => 'This wish cannot be deleted.']);
        }

        DB::beginTransaction();

        try {
            // Create cancellation transaction
            $wish->wishTransactions()->create([
                'kid_id' => $kid->id,
                'family_id' => $wish->family_id,
                'transaction_type' => 'cancelled',
                'created_at' => now(),
            ]);

            // Update status
            $wish->update(['status' => 'cancelled']);

            // Soft delete
            $wish->delete();

            DB::commit();

            return redirect()->route('kid.dashboard')
                ->with('success', 'Wish deleted successfully!')
                ->with('active_tab', 'wishes');

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors(['error' => 'Failed to delete wish.']);
        }
    }

    /**
     * Kid requests parent to purchase wish
     */
    public function requestPurchase(Request $request, Wish $wish)
    {
        $kid = Auth::guard('kid')->user();

        if ($wish->kid_id !== $kid->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if (!$wish->canBeRequested()) {
            return response()->json([
                'success' => false,
                'message' => $wish->status !== 'saved'
                    ? 'This wish has already been requested.'
                    : 'You don\'t have enough money in your account yet.'
            ]);
        }

        // Optional fees (taxes/shipping) the kid is flagging for the parent
        $fees = max(0, (float) $request->input('fees', 0));
        $note = $fees > 0
            ? 'Kid noted estimated fees (tax/shipping): $' . number_format($fees, 2)
            : null;

        DB::beginTransaction();

        try {
            $wish->update([
                'status' => 'pending_approval',
                'requested_at' => now(),
            ]);

            $wish->wishTransactions()->create([
                'kid_id' => $kid->id,
                'family_id' => $wish->family_id,
                'transaction_type' => 'requested',
                'note' => $note,
                'created_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Request sent to your parent!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return response()->json(['success' => false, 'message' => 'Failed to send request.']);
        }
    }

    /**
     * Kid sends reminder to parent (24hr cooldown)
     */
    public function remindParent(Wish $wish)
    {
        $kid = Auth::guard('kid')->user();

        if ($wish->kid_id !== $kid->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if (!$wish->canRemindParent()) {
            return response()->json([
                'success' => false,
                'message' => 'You need to wait 24 hours before sending another reminder.'
            ]);
        }

        DB::beginTransaction();

        try {
            $wish->update(['last_reminded_at' => now()]);

            $wish->wishTransactions()->create([
                'kid_id' => $kid->id,
                'family_id' => $wish->family_id,
                'transaction_type' => 'reminded',
                'created_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reminder sent to your parent!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return response()->json(['success' => false, 'message' => 'Failed to send reminder.']);
        }
    }

    /**
     * Kid re-asks parent after a decline (allowed after 24-hour cooldown).
     * Resets wish to pending_approval and clears declined_at.
     */
    public function reAskParent(Wish $wish)
    {
        $kid = Auth::guard('kid')->user();

        if ($wish->kid_id !== $kid->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if (!$wish->canReAsk()) {
            return response()->json([
                'success' => false,
                'message' => 'You need to wait 24 hours before asking again.'
            ]);
        }

        DB::beginTransaction();

        try {
            $wish->update([
                'status' => 'pending_approval',
                'requested_at' => now(),
                'declined_at' => null,
            ]);

            $wish->wishTransactions()->create([
                'kid_id' => $kid->id,
                'family_id' => $wish->family_id,
                'transaction_type' => 'requested',
                'note' => 'Kid re-asked after decline',
                'created_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Request sent to your parent again!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return response()->json(['success' => false, 'message' => 'Failed to send request.']);
        }
    }

    // ============================================
    // PARENT ROUTES
    // ============================================

    /**
     * Display all wishes for a kid (parent view)
     */
    public function parentIndex(Kid $kid)
    {
        // Authorization: verify parent has access to this kid
        $familyIds = Auth::user()->families()->pluck('families.id');
        if (!$familyIds->contains($kid->family_id)) {
            abort(403, 'Unauthorized');
        }

        $pendingWishes = $kid->wishes()
            ->where('status', 'pending_approval')
            ->orderBy('requested_at', 'desc')
            ->get();

        $allWishes = $kid->wishes()
            ->whereIn('status', ['saved', 'pending_approval', 'declined'])
            ->orderBy('created_at', 'desc')
            ->get();

        $purchasedWishes = $kid->wishes()
            ->where('status', 'purchased')
            ->orderBy('purchased_at', 'desc')
            ->paginate(10);

        return view('wishes.parent-index', compact('kid', 'pendingWishes', 'allWishes', 'purchasedWishes'));
    }

    /**
     * Show the form for creating a new wish (parent view)
     */
    public function parentCreate(Kid $kid)
    {
        // Authorization: verify parent has access to this kid
        $familyIds = Auth::user()->families()->pluck('families.id');
        if (!$familyIds->contains($kid->family_id)) {
            abort(403, 'Unauthorized');
        }

        return view('wishes.parent-create', compact('kid'));
    }

    /**
     * Display wish details (parent view)
     */
    public function parentShow(Wish $wish)
    {
        // Authorization: verify parent has access to this kid
        $familyIds = Auth::user()->families()->pluck('families.id');
        if (!$familyIds->contains($wish->family_id)) {
            abort(403, 'Unauthorized');
        }

        $kid = $wish->kid;
        $wish->load('wishTransactions');

        return view('wishes.parent-show', compact('wish', 'kid'));
    }

    /**
     * Parent creates wish for kid
     */
    public function parentStore(Request $request, Kid $kid)
    {
        // Authorization check
        $familyIds = Auth::user()->families()->pluck('families.id');
        if (!$familyIds->contains($kid->family_id)) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'item_url' => 'nullable|url|max:2000',
            'price' => 'required|numeric|min:0|max:99999.99',
            'reason' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:5120',
            'scraped_image_url' => 'nullable|url',
        ]);

        DB::beginTransaction();

        try {
            $imagePath = null;

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('wish-photos', 'public');
            } elseif ($request->filled('scraped_image_url')) {
                $imagePath = $this->urlScraper->downloadImage($request->scraped_image_url);
            }

            $wish = $kid->wishes()->create([
                'family_id' => $kid->family_id,
                'created_by_user_id' => Auth::id(),
                'item_name' => $validated['item_name'],
                'item_url' => $validated['item_url'],
                'image_path' => $imagePath,
                'price' => $validated['price'],
                'reason' => $validated['reason'],
                'status' => 'saved',
            ]);

            $wish->wishTransactions()->create([
                'kid_id' => $kid->id,
                'family_id' => $kid->family_id,
                'performed_by_user_id' => Auth::id(),
                'transaction_type' => 'created',
                'note' => 'Created by parent',
                'created_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('kids.wishes', $kid)
                ->with('success', 'Wish created for ' . $kid->name . '!');

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            \Log::error('Failed to create wish: ' . $e->getMessage(), [
                'exception' => $e,
                'kid_id' => $kid->id,
                'request' => $request->all()
            ]);
            return back()->withErrors(['error' => 'Failed to create wish.'])->withInput();
        }
    }

    /**
     * Parent approves purchase request
     */
    public function approvePurchase(Request $request, Wish $wish)
    {
        // Authorization check
        $familyIds = Auth::user()->families()->pluck('families.id');
        if (!$familyIds->contains($wish->family_id)) {
            abort(403, 'Unauthorized');
        }

        if ($wish->status !== 'pending_approval') {
            return back()->withErrors(['error' => 'This wish is not pending approval.']);
        }

        // Use adjusted amount (base price + any fees) if provided
        $adjustedAmount = $request->input('adjusted_amount');
        $totalAmount = ($adjustedAmount && is_numeric($adjustedAmount) && floatval($adjustedAmount) >= $wish->price)
            ? round(floatval($adjustedAmount), 2)
            : $wish->price;
        $hasFees = $totalAmount > $wish->price;

        DB::beginTransaction();

        try {
            $kid = $wish->kid;

            // Check balance
            if ($kid->balance < $totalAmount) {
                return back()->withErrors(['error' => $kid->name . ' does not have enough balance for this purchase.']);
            }

            // Deduct from kid balance
            $kid->balance -= $totalAmount;
            $kid->save();

            // Create transaction ledger entry
            $description = 'Wish purchase: ' . $wish->item_name;
            if ($hasFees) {
                $description .= ' (includes fees)';
            }
            $kid->transactions()->create([
                'type' => 'spend',
                'amount' => $totalAmount,
                'description' => $description,
                'category' => 'wish_purchase',
                'initiated_by' => 'parent',
            ]);

            // Update wish status
            $wish->update([
                'status' => 'purchased',
                'purchased_at' => now(),
                'purchased_by_user_id' => Auth::id(),
            ]);

            // Build note for wish transaction
            $note = null;
            if ($hasFees) {
                $fees = round($totalAmount - $wish->price, 2);
                $note = 'Includes fees (tax/shipping): $' . number_format($fees, 2);
            }

            // Create wish transaction
            $wish->wishTransactions()->create([
                'kid_id' => $kid->id,
                'family_id' => $wish->family_id,
                'performed_by_user_id' => Auth::id(),
                'transaction_type' => 'purchased',
                'note' => $note,
                'created_at' => now(),
            ]);

            DB::commit();

            return back()->with('success', 'Purchase approved! $' . number_format($totalAmount, 2) . ' deducted from ' . $kid->name . '\'s balance.');

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors(['error' => 'Failed to approve purchase.']);
        }
    }

    /**
     * Parent declines purchase request
     */
    public function declinePurchase(Request $request, Wish $wish)
    {
        // Authorization check
        $familyIds = Auth::user()->families()->pluck('families.id');
        if (!$familyIds->contains($wish->family_id)) {
            abort(403, 'Unauthorized');
        }

        if ($wish->status !== 'pending_approval') {
            return back()->withErrors(['error' => 'This wish is not pending approval.']);
        }

        DB::beginTransaction();

        try {
            $wish->update([
                'status' => 'declined',
                'declined_at' => now(),
            ]);

            $wish->wishTransactions()->create([
                'kid_id' => $wish->kid_id,
                'family_id' => $wish->family_id,
                'performed_by_user_id' => Auth::id(),
                'transaction_type' => 'declined',
                'note' => $request->input('reason'),
                'created_at' => now(),
            ]);

            DB::commit();

            return back()->with('success', 'Purchase request declined.');

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors(['error' => 'Failed to decline purchase.']);
        }
    }

    /**
     * Parent redeems wish - deducts from kid's balance
     * Use this when parent wants to purchase a saved wish (not requested by kid)
     */
    public function redeemWish(Wish $wish)
    {
        // Authorization check
        $familyIds = Auth::user()->families()->pluck('families.id');
        if (!$familyIds->contains($wish->family_id)) {
            abort(403, 'Unauthorized');
        }

        if ($wish->isPurchased()) {
            return back()->withErrors(['error' => 'This wish has already been purchased.']);
        }

        DB::beginTransaction();

        try {
            $kid = $wish->kid;

            // Use adjusted amount if provided, otherwise use wish price
            $amount = request('adjusted_amount') ? floatval(request('adjusted_amount')) : $wish->price;

            // Check balance
            if ($kid->balance < $amount) {
                return back()->withErrors(['error' => $kid->name . ' does not have enough balance for this purchase.']);
            }

            // Deduct from kid balance
            $kid->balance -= $amount;
            $kid->save();

            // Create transaction ledger entry
            $kid->transactions()->create([
                'type' => 'spend',
                'amount' => $amount,
                'description' => 'Wish purchase: ' . $wish->item_name . ($amount != $wish->price ? ' (includes fees)' : ''),
                'category' => 'wish_purchase',
                'initiated_by' => 'parent',
            ]);

            // Update wish status
            $wish->update([
                'status' => 'purchased',
                'purchased_at' => now(),
                'purchased_by_user_id' => Auth::id(),
            ]);

            // Create wish transaction
            $wish->wishTransactions()->create([
                'kid_id' => $wish->kid_id,
                'family_id' => $wish->family_id,
                'performed_by_user_id' => Auth::id(),
                'transaction_type' => 'purchased',
                'note' => $amount != $wish->price ? 'Adjusted amount: $' . number_format($amount, 2) : null,
                'created_at' => now(),
            ]);

            DB::commit();

            return back()->with('success', 'Wish redeemed! $' . number_format($amount, 2) . ' deducted from ' . $kid->name . '\'s balance.');

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors(['error' => 'Failed to redeem wish.']);
        }
    }

    /**
     * AJAX endpoint for URL scraping
     */
    public function scrapeUrl(Request $request)
    {
        // Increase timeout for slow sites like Target
        set_time_limit(90);

        $request->validate(['url' => 'required|url']);

        $result = $this->urlScraper->scrapeUrl($request->url);

        return response()->json($result);
    }

    /**
     * Parent deletes a wish (typically for declined wishes)
     */
    public function parentDestroy(Wish $wish)
    {
        // Authorization check
        $familyIds = Auth::user()->families()->pluck('families.id');
        if (!$familyIds->contains($wish->family_id)) {
            abort(403, 'Unauthorized');
        }

        // Store kid for redirect
        $kid = $wish->kid;

        // Delete the wish
        $wish->delete();

        return redirect()->route('kids.wishes', $kid)
            ->with('success', 'Wish deleted successfully.');
    }
}

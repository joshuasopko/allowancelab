@extends('layouts.parent')

@section('title', 'Create Wish for ' . $kid->name . ' - AllowanceLab')

@section('content')
<div class="page-header">
    <h1 class="page-title">Create Wish for {{ $kid->name }}</h1>
    <a href="{{ route('parent.wishes.index', $kid) }}" class="btn-back">
        <i class="fas fa-arrow-left"></i> Back to Wish List
    </a>
</div>

<!-- Success/Error Messages -->
@if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        {{ $errors->first() }}
    </div>
@endif

<div class="form-card">
    <form action="{{ route('parent.wishes.store', $kid) }}" method="POST" enctype="multipart/form-data" id="createWishForm">
        @csrf

        <!-- URL Input with Scrape Button -->
        <div class="form-group">
            <label for="wish_url">Item URL (optional)</label>
            <div class="input-with-button">
                <input type="url"
                       id="wish_url"
                       name="item_url"
                       class="form-input"
                       placeholder="https://www.amazon.com/..."
                       value="{{ old('item_url') }}">
                <button type="button" onclick="scrapeWishUrl()" id="scrapeBtn" class="btn-scrape">
                    <i class="fas fa-magic"></i> Auto-fill
                </button>
            </div>
            <p class="input-hint">Paste a link and we'll try to auto-fill the details! Works best with Target, Walmart, and most online stores.</p>
            <div id="scrapeError" class="input-error" style="display: none;"></div>
            <div id="scrapePartialSuccess" class="input-hint" style="display: none; color: #f59e0b; margin-top: 8px;">
                <i class="fas fa-info-circle"></i> <span id="scrapePartialMessage"></span>
            </div>
        </div>

        <!-- Item Name -->
        <div class="form-group">
            <label for="item_name">Item Name *</label>
            <input type="text"
                   id="item_name"
                   name="item_name"
                   class="form-input"
                   placeholder="e.g., New Bike"
                   required
                   value="{{ old('item_name') }}">
            @error('item_name')
                <p class="input-error">{{ $message }}</p>
            @enderror
        </div>

        <!-- Price -->
        <div class="form-group">
            <label for="price">Price *</label>
            <div class="input-prefix-wrapper">
                <span class="input-prefix">$</span>
                <input type="number"
                       id="price"
                       name="price"
                       class="form-input input-with-prefix"
                       placeholder="0.00"
                       step="0.01"
                       min="0"
                       max="99999.99"
                       required
                       value="{{ old('price') }}">
            </div>
            @error('price')
                <p class="input-error">{{ $message }}</p>
            @enderror
        </div>

        <!-- Image Preview & Upload -->
        <div class="form-group">
            <label>Image (optional)</label>
            <div id="imagePreviewContainer" style="display: none; margin-bottom: 12px;">
                <img id="imagePreview" src="" alt="Preview" class="image-preview">
                <button type="button" onclick="removeImagePreview()" class="btn-remove-image">
                    <i class="fas fa-times"></i> Remove Image
                </button>
            </div>
            <input type="hidden" id="scraped_image_url" name="scraped_image_url">
            <input type="file"
                   id="image"
                   name="image"
                   accept="image/*"
                   class="file-input"
                   onchange="previewUploadedImage(this)">
            <label for="image" class="btn-upload">
                <i class="fas fa-cloud-upload-alt"></i> Upload Image
            </label>
        </div>

        <!-- Reason/Note -->
        <div class="form-group">
            <label for="reason">Note (optional)</label>
            <textarea id="reason"
                      name="reason"
                      class="form-input"
                      rows="3"
                      placeholder="Add any notes about this wish...">{{ old('reason') }}</textarea>
            @error('reason')
                <p class="input-error">{{ $message }}</p>
            @enderror
        </div>

        <!-- Balance Info -->
        <div class="balance-info">
            <i class="fas fa-wallet"></i>
            <span>{{ $kid->name }}'s current balance: <strong>${{ number_format($kid->balance, 2) }}</strong></span>
        </div>

        <!-- Submit Buttons -->
        <div class="form-actions">
            <a href="{{ route('parent.wishes.index', $kid) }}" class="btn-cancel">Cancel</a>
            <button type="submit" class="btn-submit">
                <i class="fas fa-gift"></i> Create Wish
            </button>
        </div>
    </form>
</div>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.page-title {
    font-size: 28px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.btn-back {
    background: #6b7280;
    color: white;
    padding: 10px 16px;
    border-radius: 8px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    font-size: 14px;
    transition: background 0.2s;
}

.btn-back:hover {
    background: #4b5563;
}

.alert {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #6ee7b7;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
}

.form-card {
    background: white;
    border-radius: 12px;
    padding: 32px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    max-width: 800px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
    font-size: 14px;
}

.form-input {
    width: 100%;
    padding: 12px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 15px;
    color: #1f2937;
    transition: border-color 0.2s;
    box-sizing: border-box;
}

.form-input:focus {
    outline: none;
    border-color: #3b82f6;
}

.input-with-button {
    display: flex;
    gap: 8px;
}

.input-with-button .form-input {
    flex: 1;
}

.btn-scrape {
    background: #3b82f6;
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: background 0.2s;
}

.btn-scrape:hover {
    background: #2563eb;
}

.btn-scrape:disabled {
    background: #9ca3af;
    cursor: not-allowed;
}

.input-hint {
    font-size: 13px;
    color: #6b7280;
    margin-top: 6px;
    margin-bottom: 0;
}

.input-error {
    font-size: 13px;
    color: #ef4444;
    margin-top: 6px;
    background: #fee2e2;
    padding: 8px 12px;
    border-radius: 6px;
    border: 1px solid #fca5a5;
}

.input-prefix-wrapper {
    position: relative;
}

.input-prefix {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #6b7280;
    font-weight: 600;
    pointer-events: none;
    font-size: 15px;
}

.input-with-prefix {
    padding-left: 32px;
}

.image-preview {
    max-width: 300px;
    border-radius: 8px;
    display: block;
    margin-bottom: 8px;
}

.btn-remove-image {
    background: none;
    border: none;
    padding: 6px 12px;
    cursor: pointer;
    font-size: 14px;
    color: #ef4444;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: color 0.2s;
}

.btn-remove-image:hover {
    color: #dc2626;
}

.file-input {
    display: none;
}

.btn-upload {
    background: #e5e7eb;
    color: #374151;
    border: 2px solid #d1d5db;
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.btn-upload:hover {
    background: #f3f4f6;
    border-color: #9ca3af;
}

.balance-info {
    background: #dbeafe;
    color: #1e40af;
    border: 1px solid #93c5fd;
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid #e5e7eb;
}

.btn-cancel {
    background: #f3f4f6;
    color: #374151;
    padding: 14px 24px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 16px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: background 0.2s;
}

.btn-cancel:hover {
    background: #e5e7eb;
}

.btn-submit {
    background: #3b82f6;
    color: white;
    padding: 14px 24px;
    border-radius: 8px;
    border: none;
    font-weight: 600;
    font-size: 16px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: background 0.2s;
}

.btn-submit:hover {
    background: #2563eb;
}

/* Mobile responsiveness */
@media (max-width: 640px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .form-card {
        padding: 20px;
    }

    .input-with-button {
        flex-direction: column;
    }

    .btn-scrape {
        width: 100%;
        justify-content: center;
    }

    .form-actions {
        flex-direction: column;
    }

    .btn-cancel,
    .btn-submit {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
let isScrapingWish = false;

function scrapeWishUrl() {
    const url = document.getElementById('wish_url').value.trim();
    const scrapeBtn = document.getElementById('scrapeBtn');
    const scrapeError = document.getElementById('scrapeError');
    const scrapePartialSuccess = document.getElementById('scrapePartialSuccess');

    scrapeError.style.display = 'none';
    scrapePartialSuccess.style.display = 'none';

    if (!url) {
        scrapeError.textContent = 'Please enter a URL first';
        scrapeError.style.display = 'block';
        return;
    }

    if (isScrapingWish) return;

    isScrapingWish = true;
    scrapeBtn.disabled = true;
    scrapeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';

    fetch('{{ route("parent.wishes.scrape-url", $kid) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ url: url })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Server returned error: ' + response.status);
        }
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Server did not return JSON. This may be a routing or authentication issue.');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            if (data.data.title) {
                document.getElementById('item_name').value = data.data.title;
            }
            if (data.data.price) {
                document.getElementById('price').value = parseFloat(data.data.price).toFixed(2);
            }
            if (data.data.image_url) {
                document.getElementById('scraped_image_url').value = data.data.image_url;
                document.getElementById('imagePreview').src = data.data.image_url;
                document.getElementById('imagePreviewContainer').style.display = 'block';
            }

            if (data.partial) {
                document.getElementById('scrapePartialMessage').textContent = data.message;
                scrapePartialSuccess.style.display = 'block';
            }
        } else {
            scrapeError.textContent = data.message || 'Failed to auto-fill from URL';
            scrapeError.style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Scrape error:', error);
        scrapeError.textContent = 'Failed to auto-fill. Please enter details manually.';
        scrapeError.style.display = 'block';
    })
    .finally(() => {
        isScrapingWish = false;
        scrapeBtn.disabled = false;
        scrapeBtn.innerHTML = '<i class="fas fa-magic"></i> Auto-fill';
    });
}

function previewUploadedImage(input) {
    if (input.files && input.files[0]) {
        // Clear any scraped image
        document.getElementById('scraped_image_url').value = '';

        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagePreview').src = e.target.result;
            document.getElementById('imagePreviewContainer').style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeImagePreview() {
    document.getElementById('imagePreview').src = '';
    document.getElementById('imagePreviewContainer').style.display = 'none';
    document.getElementById('image').value = '';
    document.getElementById('scraped_image_url').value = '';
}
</script>
@endsection

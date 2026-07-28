<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label required">Autore</label>
        <input type="text" name="author_name" value="{{ old('author_name', $testimonial?->author_name ?? '') }}" class="form-control @error('author_name') is-invalid @enderror">
        @error('author_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Cliente da</label>
        <input type="text" name="member_since" value="{{ old('member_since', $testimonial?->member_since ?? '') }}" class="form-control @error('member_since') is-invalid @enderror" placeholder="Cliente dal 2022">
        @error('member_since')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 mb-3">
        <label class="form-label required">Testo</label>
        <textarea name="text" rows="4" class="form-control @error('text') is-invalid @enderror">{{ old('text', $testimonial?->text ?? '') }}</textarea>
        @error('text')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

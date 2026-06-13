function filterCategory(cat) {
    window.location.href = 'user_page.php?category=' + cat;
}

//gets image preview
document.getElementById('imgInput').addEventListener('change', function() {
    const preview = document.getElementById('img-preview');
    preview.innerHTML = '';
    const files = Array.from(this.files).slice(0, 10);
    files.forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.createElement('img');
            img.src = e.target.result;
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
});

//checking list of images
function openGallery(listingId) {
    fetch('get_images.php?listing_id=' + listingId)
        .then(r => r.json())
        .then(images => {
            const container = document.getElementById('gallery-images');
            container.innerHTML = '';
            images.forEach(filename => {
                const img = document.createElement('img');
                img.src = 'Images/listings/' + filename;
                container.appendChild(img);
            });
            document.getElementById('gallery-modal').classList.remove('hidden');
        });
}

// Open edit modal pre-filled with listing data
function openEditModal(id, title, price, category, description) {
    document.getElementById('edit-listing-id').value   = id;
    document.getElementById('edit-title').value        = title;
    document.getElementById('edit-price').value        = price;
    document.getElementById('edit-category').value     = category;
    document.getElementById('edit-description').value  = description;
    document.getElementById('edit-listing-modal').classList.remove('hidden');
}

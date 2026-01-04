<!DOCTYPE html>
<html>
<head>
    <title>Products</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@latest/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto py-8">
        <h1 class="text-3xl font-bold mb-6">Products</h1>

        <div id="product-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            <!-- Products will be injected here -->
        </div>
    </div>

    <script src="{{ asset('js/upload.js') }}"></script>
    <script>
    async function fetchProducts(page = 1) {
        const resp = await fetch(`/api/products?page=${page}`);
        const json = await resp.json();
        return json;
    }

    function renderProducts(products) {
        const container = document.getElementById('product-grid');
        container.innerHTML = '';
        for (const p of products) {
            const div = document.createElement('div');
            div.className = 'bg-white rounded shadow p-4';
            let imgHtml = `
                <div class="w-full h-48 bg-gray-200 flex items-center justify-center text-gray-500">
                    No Image
                </div>
            `;
            if (p.image) {
                const src = p.image.variants['256'] || p.image.original;
                imgHtml = `<img src="${src}" alt="${p.name}" class="w-full h-48 object-cover rounded">`;
            }
            div.innerHTML = `
                ${imgHtml}
                <h2 class="text-lg font-semibold mt-2">${p.name}</h2>
                <p class="text-sm text-gray-600">${p.description || ''}</p>
                <p class="text-blue-500 font-bold mt-1">$${Number(p.price || 0).toFixed(2)}</p>
            `;
            container.appendChild(div);
        }
    }

    document.addEventListener('DOMContentLoaded', async () => {
        const resp = await fetchProducts();
        renderProducts(resp.data);
    });
    </script>
</body>
</html>

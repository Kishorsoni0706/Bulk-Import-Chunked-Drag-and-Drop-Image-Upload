# Bulk Import + Chunked Drag-and-Drop Image Upload

## Setup

1. Clone repo  
2. `composer install`  
3. `npm install && npm run dev`  
4. Configure `.env` (DB, queue, filesystem)  
5. Run migrations: `php artisan migrate`  
6. Setup queue worker: `php artisan queue:work`  
7. Serve: `php artisan serve`

## Usage

### Import Products via CSV

- API: `POST /api/import/products` with file input `csv_file`  
- CSV must have headers including at least `sku` and `name`. Optionally `price`, `description`, `image_upload_uuid`  
- Get `import_id` from response  
- Poll `GET /api/import/results/{import_id}` for summary

### Upload Image in Chunks

- Use frontend JS to chunk the image and send POST `/api/upload/chunk`, then `/api/upload/complete`  
- After image is merged, variants are generated, and linking job will run to attach this upload to product(s) with matching `image_upload_uuid`

### View Products

- Visit `/products` (Blade view)  
- Or GET `/api/products` returns paginated JSON  

## Notes

- Uses `jobtech/laravel-chunky` for chunking (install via composer)  
- Uses `intervention/image` for image resizing  
- Queues must be running  
- Storage `public` should be linked: `php artisan storage:link`


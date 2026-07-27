<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GalleryStoreRequest;
use App\Http\Requests\Admin\GalleryUpdateRequest;
use App\Models\GalleryImage;
use App\Support\ImageStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class GalleryImageController extends Controller
{
    public function index(): View
    {
        return view('admin.galleries.index', [
            'galleryImages' => GalleryImage::query()->ordered()->get(),
        ]);
    }

    public function store(GalleryStoreRequest $request): RedirectResponse
    {
        $storedPaths = [];
        $nextOrder = ((int) GalleryImage::query()->max('display_order')) + 10;

        try {
            DB::transaction(function () use ($request, &$storedPaths, &$nextOrder): void {
                foreach ($request->file('images', []) as $image) {
                    $path = ImageStorage::store($image, 'gallery');
                    $storedPaths[] = $path;

                    GalleryImage::create([
                        'image_path' => $path,
                        'display_order' => $nextOrder,
                        'is_visible' => true,
                    ]);

                    $nextOrder += 10;
                }
            });
        } catch (Throwable $exception) {
            foreach ($storedPaths as $path) {
                ImageStorage::delete($path);
            }

            throw $exception;
        }

        return redirect()
            ->route('admin.galleries.index')
            ->with('status', count($storedPaths).' foto berhasil ditambahkan ke galeri.');
    }

    public function update(GalleryUpdateRequest $request): RedirectResponse
    {
        $items = $request->validated('items');
        $existingIds = GalleryImage::query()
            ->whereKey(array_keys($items))
            ->pluck('id')
            ->all();

        DB::transaction(function () use ($items, $existingIds): void {
            foreach ($existingIds as $id) {
                GalleryImage::query()->whereKey($id)->update([
                    'display_order' => $items[$id]['display_order'],
                    'is_visible' => $items[$id]['is_visible'],
                ]);
            }
        });

        return redirect()
            ->route('admin.galleries.index')
            ->with('status', 'Urutan dan status galeri berhasil disimpan.');
    }

    public function destroy(GalleryImage $galleryImage): RedirectResponse
    {
        $imagePath = $galleryImage->image_path;

        $galleryImage->delete();
        ImageStorage::delete($imagePath);

        return redirect()
            ->route('admin.galleries.index')
            ->with('status', 'Foto berhasil dihapus dari galeri.');
    }
}

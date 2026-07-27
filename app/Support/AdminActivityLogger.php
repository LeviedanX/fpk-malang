<?php

namespace App\Support;

use App\Models\AdminActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AdminActivityLogger
{
    public function __construct(private readonly AdminDeviceIdentity $deviceIdentity) {}

    /**
     * Logging keamanan tidak boleh membuat request utama gagal.
     *
     * @param  array<string, mixed>|null  $metadata
     */
    public function log(
        Request $request,
        string $event,
        string $description,
        ?User $user = null,
        ?int $statusCode = null,
        ?array $metadata = null,
    ): void {
        try {
            if (! Schema::hasTable('admin_activity_logs')) {
                return;
            }

            AdminActivityLog::create([
                'user_id' => $user?->getKey(),
                'event' => mb_substr($event, 0, 100),
                'description' => mb_substr($description, 0, 255),
                'route_name' => $request->route()?->getName(),
                'method' => $request->method(),
                'path' => mb_substr('/'.$request->path(), 0, 255),
                'ip_address' => $request->ip(),
                'device_key' => $this->deviceIdentity->key($request),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
                'status_code' => $statusCode,
                'metadata' => $metadata,
            ]);
        } catch (Throwable) {
            // Audit trail bersifat best-effort agar kegagalan tabel/cache tidak
            // mengunci administrator dari aplikasi.
        }
    }

    public function descriptionFor(Request $request): string
    {
        $route = (string) $request->route()?->getName();

        return match ($route) {
            'admin.dashboard' => 'Membuka dashboard admin.',
            'admin.articles.index' => 'Membuka daftar artikel.',
            'admin.articles.create' => 'Membuka formulir artikel baru.',
            'admin.articles.store' => 'Membuat artikel baru.',
            'admin.articles.edit' => 'Membuka formulir perubahan artikel.',
            'admin.articles.update' => 'Memperbarui artikel.',
            'admin.articles.destroy' => 'Mengarsipkan artikel.',
            'admin.articles.archive' => 'Membuka arsip artikel.',
            'admin.articles.restore' => 'Memulihkan artikel.',
            'admin.articles.force-delete' => 'Menghapus artikel secara permanen.',
            'admin.agendas.index' => 'Membuka daftar agenda.',
            'admin.agendas.create' => 'Membuka formulir agenda baru.',
            'admin.agendas.store' => 'Membuat agenda baru.',
            'admin.agendas.edit' => 'Membuka formulir perubahan agenda.',
            'admin.agendas.update' => 'Memperbarui agenda.',
            'admin.agendas.destroy' => 'Mengarsipkan agenda.',
            'admin.agendas.archive' => 'Membuka arsip agenda.',
            'admin.agendas.history' => 'Membuka riwayat agenda.',
            'admin.agendas.logs' => 'Membuka log agenda.',
            'admin.agendas.restore' => 'Memulihkan agenda.',
            'admin.agendas.force-delete' => 'Menghapus agenda secara permanen.',
            'admin.galleries.index' => 'Membuka pengelolaan galeri.',
            'admin.galleries.store' => 'Mengunggah foto galeri.',
            'admin.galleries.update' => 'Memperbarui susunan galeri.',
            'admin.galleries.destroy' => 'Menghapus foto galeri.',
            'admin.periods.index' => 'Membuka daftar periode pengurus.',
            'admin.periods.create' => 'Membuka formulir periode pengurus.',
            'admin.periods.store' => 'Membuat periode pengurus.',
            'admin.periods.edit' => 'Membuka perubahan periode pengurus.',
            'admin.periods.update' => 'Memperbarui periode pengurus.',
            'admin.periods.destroy' => 'Menghapus periode pengurus.',
            'admin.members.index' => 'Membuka daftar anggota pengurus.',
            'admin.members.create' => 'Membuka formulir anggota pengurus.',
            'admin.members.store' => 'Menambahkan anggota pengurus.',
            'admin.members.edit' => 'Membuka perubahan anggota pengurus.',
            'admin.members.update' => 'Memperbarui anggota pengurus.',
            'admin.members.destroy' => 'Menghapus anggota pengurus.',
            'admin.members.group_photo' => 'Memperbarui foto bersama pengurus.',
            'admin.members.group_photo.destroy' => 'Menghapus foto bersama pengurus.',
            'admin.settings.edit' => 'Membuka pengaturan website.',
            'admin.settings.update' => 'Memperbarui pengaturan website.',
            'admin.account.edit' => 'Membuka Pengaturan Admin.',
            'admin.account.unlock' => 'Memverifikasi PIN Pengaturan Admin.',
            'admin.account.pin.setup' => 'Membuat PIN Pengaturan Admin.',
            'admin.account.pin.update' => 'Mengganti PIN Pengaturan Admin.',
            'admin.account.update' => 'Memperbarui profil administrator.',
            'admin.account.password' => 'Mengganti password administrator.',
            'admin.account.logs.clear' => 'Menghapus histori aktivitas admin.',
            default => 'Mengakses fitur administrator.',
        };
    }
}

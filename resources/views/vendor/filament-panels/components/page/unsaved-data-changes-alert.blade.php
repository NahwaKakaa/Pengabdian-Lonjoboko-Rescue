{{--
    Pengganti peringatan "perubahan belum disimpan" bawaan Filament.
    Halaman formulir hanya mendaftarkan cara memeriksa apakah datanya berubah;
    dialognya sendiri ada di resources/views/filament/leave-dialog.blade.php.
--}}
@if ($this->hasUnsavedDataChangesAlert())
    @script
        <script>
            window.lrUnsavedGuards = (window.lrUnsavedGuards || []).filter((guard) => guard.alive())

            window.lrUnsavedGuards.push({
                alive: () => document.body.contains($wire.$el),
                dirty: () => {
                    if ($wire?.__instance?.effects?.redirect) {
                        return false
                    }

                    return window.jsMd5(JSON.stringify($wire.data).replace(/\\/g, '')) !== $wire.savedDataHash
                },
            })
        </script>
    @endscript
@endif

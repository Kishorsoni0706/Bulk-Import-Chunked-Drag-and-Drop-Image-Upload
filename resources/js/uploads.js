
class ChunkUploader {
    constructor(file, uploadUuid, chunkSize = 1024 * 1024) {
        this.file = file;
        this.uploadUuid = uploadUuid;
        this.chunkSize = chunkSize;
        this.totalChunks = Math.ceil(file.size / chunkSize);
    }

    async upload() {
        for (let idx = 0; idx < this.totalChunks; idx++) {
            const start = idx * this.chunkSize;
            const end = Math.min(this.file.size, start + this.chunkSize);
            const blob = this.file.slice(start, end);

            const form = new FormData();
            form.append('upload_uuid', this.uploadUuid);
            form.append('chunk_index', idx);
            form.append('total_chunks', this.totalChunks);
            form.append('filename', this.file.name);
            form.append('chunk', blob);

            await fetch('/api/upload/chunk', {
                method: 'POST',
                body: form,
                headers: {
                    'Accept': 'application/json',
                },
            });
        }

        // Compute checksum (sha256) — using SubtleCrypto
        const arrayBuf = await this.file.arrayBuffer();
        const hashBuf = await crypto.subtle.digest('SHA-256', arrayBuf);
        const hashArray = Array.from(new Uint8Array(hashBuf));
        const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');

        const comp = new FormData();
        comp.append('upload_uuid', this.uploadUuid);
        comp.append('checksum', hashHex);

        await fetch('/api/upload/complete', {
            method: 'POST',
            body: comp,
            headers: { 'Accept': 'application/json' }
        });
    }
}



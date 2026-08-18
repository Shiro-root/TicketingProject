# Cara pasang file-file ini ke repo TicketingProject

1. Copy 4 file/folder ini ke root project Laravel kamu (menimpa yang lama kalau ada):
   - Dockerfile
   - .dockerignore
   - render.yaml
   - docker/entrypoint.sh

2. File migration perlu DIGANTI ISINYA (bukan ditambah baru), karena ini perbaikan
   bug fullText index yang tidak didukung SQLite:
   - Copy isi `2024_01_01_000027_create_knowledge_base_articles_table.php`
     ke: `database/migrations/2024_01_01_000027_create_knowledge_base_articles_table.php`
     (timpa file yang sudah ada, JANGAN dianggap file migration baru)

3. Commit & push:
   git add .
   git commit -m "Add Docker setup for Render deploy + fix sqlite fulltext index bug"
   git push

4. Lanjut ke Render — bikin Web Service baru, connect ke repo ini, runtime pilih
   "Docker", pilih plan Free. Render akan otomatis baca render.yaml.

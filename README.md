# Site NEAS — tema e conteúdo

- `modules/neas-theme/` — tema WordPress (blocos) do Núcleo Espírita Auta de Souza
- `modules/neas-site/` — plugin (palestras, Pix, mapa, prévia do tema)
- `content/pages/` — cópia do conteúdo das páginas novas, exportada do WordPress
- `build.sh` — gera os zips em `dist/` para enviar ao WordPress

O texto das páginas é editado no próprio WordPress; a pasta `content/pages/` é atualizada
a partir do site antes de qualquer mudança, para não sobrescrever edições.

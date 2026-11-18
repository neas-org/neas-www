# Contexto do projeto — novo site do NEAS

Redesenho do site do Núcleo Espírita Auta de Souza (https://www.neas.org.br), **mantendo WordPress**
para que voluntários continuem publicando conteúdo. O responsável (Nilton) é iniciante em WordPress:
explique passo a passo, em português, sem jargão.

## Regras de trabalho
- **Nada pode ficar visível para visitantes até o lançamento.** As páginas novas são *privadas*;
  o tema novo está instalado mas **não ativado**. Pergunte antes de alterar qualquer conteúdo público.
- **O WordPress é a fonte da verdade dos textos.** O Nilton edita as páginas no painel. Antes de
  qualquer mudança numa página, baixe o conteúdo atual do site (`context=edit`, campo `content.raw`),
  atualize `content/pages/`, faça commit, e só então altere o trecho pedido. Nunca regenere a página
  inteira a partir dos padrões do tema — isso apaga as edições dele.
- Senhas nunca no chat nem no repositório.
- **Tudo que uma pessoa vê ou mexe fica em português**: menus do painel, rótulos, campos de formulário,
  nomes e configurações dos blocos, nomes de cores/fontes/padrões/modelos no editor, mensagens, URLs e
  âncoras. Inglês só nos identificadores internos do código (chaves, slugs técnicos, classes, funções).

## Estrutura
Organização inspirada no liferay-portal (`modules/<nome>`). Código (nomes, classes, comentários) em
inglês; textos que visitantes e voluntários veem continuam em português, assim como URLs, slugs de
páginas e âncoras (`#palestras`, `#doe`…).

- `modules/neas-theme/` — tema de blocos (theme.json v3), slug `neas-theme`. Padrões em `patterns/*.php`
  (slugs `neas/<arquivo>`), partes `parts/header.html` (→ `neas/header`) e `parts/footer.html`
  (→ `neas/footer`), modelo personalizado `full-width` (página sem faixa de título). Fontes locais
  (Poppins, Nunito Sans, Lora itálico). Classes CSS com prefixo `neas-`.
  - Cores (slugs): `blue` #0B6CA8 (principal), `blue-dark` #0A4A75, `purple` #3A3D78 (destaque),
    `ink` #1F2340, `yellow` #F6C324 (botão Pix), `light` #EEF5FB, `cream` #FFF7DC.
  - Ordem da página inicial (`patterns/home-page.php`): hero, opening-hours, upcoming-lectures,
    activities, evangelization, instagram, donations, history, visit.
  - `patterns/auta-de-souza.php` existe mas não é usado na home (o Nilton preferiu a seção "Nossa história").
- `modules/neas-site/` — plugin (v2.0.0), slug `neas-site`; código em `includes/`
  (`lectures.php`, `blocks.php`, `instagram.php`, `preview.php`):
  - Tipo de post `neas_lecture` (REST `wp/v2/lectures`, URL `/palestra/...`; título = tema; metas
    `neas_date` Y-m-d, `neas_time` H:i, `neas_speaker`), editor clássico com caixa "Dados da palestra",
    colunas no admin.
  - Blocos dinâmicos (ServerSideRender, sem build): `neas/next-lecture`, `neas/upcoming-lectures {count}`,
    `neas/lecture-schedule`, `neas/pix {key, display, qr}`, `neas/map {address}`. Sem shortcodes.
  - Fallback de `[instagram-feed]`: não mostra nada a visitantes enquanto o Smash Balloon não estiver instalado.
  - **Modo prévia**: `?neas_preview=1` (cookie `neas_preview`) — para usuários logados com `edit_pages`,
    troca o tema para `neas-theme`, usa a página `inicio` como página inicial e remove o prefixo "Privado:".
    Visitantes não são afetados. `?neas_preview=0` sai. Há botão na barra de admin
    ("Ver o site com o tema novo" / "Sair da prévia").
- `content/pages/<slug>.html` — cópia do conteúdo (HTML de blocos) das páginas novas, exportada do site.
- `build.sh` — gera `dist/<módulo>.zip` (a pasta dentro do zip = slug que o WordPress usa).
- `tools/migrate-names.py` — migração única dos nomes antigos (português) para os novos; apagar após o lançamento.

## Site em produção
- WordPress 6.7.1, PHP 7.4.33 (LiteSpeed), pt_BR, fuso America/Recife. Tema antigo: BuildPress + SiteOrigin.
  Plugins: WooCommerce + Mercado Pago, Yoast, Akismet, Loginizer, Backuply, GoSMTP.
- API REST: use `https://www.neas.org.br/?rest_route=/wp/v2/...` com autenticação básica por
  **senha de aplicativo** (usuário "Nilton Vieira", administrador). Configure a sua própria —
  não está neste repositório. O mod_security devolve 406 sem um User-Agent de navegador:
  `curl -A "Mozilla/5.0 (X11; Linux x86_64) NEAS-site-setup" -H "Accept: application/json"`.
- Não envie `template` ao atualizar páginas via REST enquanto o tema não estiver ativo (é rejeitado).

### IDs importantes
| ID | O quê |
|---|---|
| 15373 | "Início (novo)" — privada, slug `inicio` (`content/pages/inicio.html`) |
| 15374 | "Palestras" — privada, slug `palestras` |
| 15384 | "Auta de Souza (nova versão)" — privada, slug `auta-de-souza-nova` (retrato + texto original da pág. 57) |
| 15383 | Mídia: retrato de Auta de Souza |
| 57 | Página antiga "Auta de Souza" (pública; foto de 2017 quebrada) |
| 14157 | Página inicial antiga "01 - Início" |
| 15377–15380 | Palestras de teste/cadastradas |

- Pix (BR Code validado, CRC ok):
  `00020126430014br.gov.bcb.pix0114097901550001430203Pix5204000053039865802BR5925NUCLEO ESPIRITA AUTA DE S6006RECIFE62290525LucTDucLux5a6ZV8T4koNXgKS63046F4F`

## Pendências
- Receber do Nilton: QR code do Pix (seção Doações), foto dos trabalhadores (seção "Nossa história"),
  prints do site da FEB (febnet.org.br) como inspiração.
- Após mudanças no tema/plugin: `./build.sh` e o Nilton envia o zip em Plugins/Temas → Adicionar novo →
  Enviar → "Substituir o atual pelo enviado".
- **Migração para os nomes em inglês (v2.0.0) — ainda não aplicada no site.** No site ainda estão o tema
  `neas` e o plugin `neas-funcionalidades` (v1.1.0, ativo), com os nomes antigos. Passos:
  1. Baixar o conteúdo atual das páginas 15373, 15374 e 15384, salvar em `content/pages/`, commit.
  2. Rodar `tools/migrate-names.py` nesses arquivos, conferir o diff, commit.
  3. Desativar e apagar o plugin `neas-funcionalidades`; enviar e ativar `neas-site` (nunca os dois ativos).
  4. Recadastrar as palestras 15377–15380 no tipo novo (`wp/v2/lectures`, metas novas) e apagar as antigas.
  5. Enviar o tema `neas-theme` e apagar o tema `neas`.
  6. Atualizar as 3 páginas via REST com o conteúdo migrado; testar com `?neas_preview=1`.

## Checklist do lançamento
1. Backup (Backuply).
2. Ativar o tema `neas-theme`.
3. Publicar 15373 (renomear para "Início") e definir como página inicial; publicar 15374.
4. Passar o conteúdo de 15384 para a página 57 e apagar a 15384.
5. Redirecionar `/paletras-publicas/` → `/palestras/`.
6. Instalar e conectar Smash Balloon Instagram Feed (@neautadesouza) e trocar o shortcode pelo bloco.
7. Verificar se o WooCommerce é usado; desativar o que não for (BuildPress, SiteOrigin, WooCommerce).
8. Apagar a cópia do WP Staging em `/grissom/`.
9. Sugerir atualizar o PHP (7.4 está sem suporte).
10. Opcional: revogar a senha de aplicativo usada no desenvolvimento.

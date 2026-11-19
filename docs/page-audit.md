# Levantamento das páginas do site antigo

Feito em 2026-11-18 a partir da API do WordPress (57 páginas). **Nada foi apagado.** A limpeza fica
para depois do lançamento, quando as páginas antigas deixarem de ter link na navegação.

Legenda da coluna **Menu**: está no menu principal atual (mnuPrincipal). **Fotos quebradas**: os
arquivos das imagens não existem mais no servidor (mesmo problema da foto antiga da Auta).

## 1. Remodelar no novo design

| ID | Página | Endereço | Situação | Proposta |
|---|---|---|---|---|
| 45 | NEAS (Nossa História) | `/neas/` | Menu. 6 fotos quebradas; vídeo "NEAS 40 Anos – Parte 1" funciona | **Feito:** nova versão privada 15405 (`/neas-nova/`), com o vídeo dos 55 anos em destaque, linha do tempo com o texto original e o vídeo dos 40 anos. Faltam as fotos. No lançamento: passar o conteúdo para a 45 |
| 57 | Auta de Souza | `/auta-de-souza/` | Menu. Foto quebrada | **Feito:** nova versão privada 15384 |
| 69 | Atividades do NEAS | `/atividades-do-neas/` | Menu; a home nova aponta para ela | **Feito:** nova versão privada 15418 (`/atividades-do-neas-nova/`), mesmos dados organizados em cartões por grupo. O Nilton vai revisar atividades que mudaram ou acabaram |
| 13117 | Atendimento Fraterno | `/atendimento-fraterno/` | O conteúdo são só 6 imagens, sem texto | Remodelar em texto (o que é, horários, como funciona) |
| 62 | Administração | `/administracao/` | Menu. Texto curto + organograma em PDF | Juntar com as coordenadorias numa página só, "Nossa organização" |
| 138, 158, 168, 172, 589–594 | Coordenadorias: CCG, CEME, CESDE, CECJ, CED, CAE, CAPS, CTG, CPT, CEM | `/ccg/`, `/ceme/`… | Textos curtos ("Nossos objetivos") + PDF | Virar seções da página "Nossa organização"; os PDFs continuam como anexo |
| 10977 | GEM (Grupo de Estudo da Mediunidade) | `/cem/gem/` | Texto curto | Seção dentro da CEM na página "Nossa organização" |
| 12683 | Política de privacidade | `/politica-de-privacidade-cdps/` | Texto-modelo em inglês | Escrever uma política de privacidade de verdade, em português (LGPD) |

## 2. Já substituídas pelas páginas novas (apagar ou redirecionar depois do lançamento)

| ID | Página | Endereço | Substituída por |
|---|---|---|---|
| 14157 | 01 - Início | `/14157-2/` (página inicial atual) | 15373 Início (novo) |
| 74 | 01 - Página Inicial | `/01-pagina-inicial/` | 15373 (versão de 2021, avisos de palestra online) |
| 10840 | Palestras Públicas | `/paletras-publicas/` | 15374 Palestras (redirecionar `/paletras-publicas/` → `/palestras/`) |
| 12755 | Pagamentos, Mensalidades e Doações | `/mensalidades-e-doacoes/` | Seção "Doações" da home (a página antiga está vazia) |

## 3. Decidir com o Nilton

| ID | Página | Endereço | Observação |
|---|---|---|---|
| 11977 | Curso de Passe 2019 | `/curso-de-passe-2019/` | Material do curso (5 PDFs, 31 vídeos). Ainda é usado? Pode virar uma página de "Materiais de estudo" |
| 11025–10984 | GEM – Ciclos 2010 a 2017/2018 (8 páginas) | `/cem/gem/ciclo-…/` | Listas de aulas antigas. Arquivo histórico ou apagar? |
| 12687, 12678, 12722, 12679, 12745, 306 | Loja WooCommerce: Página inicial da loja, Carrinho (2x), Finalizar compra (2x), Minha conta | `/pagina-inicial/`, `/carrinho/`… | **A loja ainda é usada:** vende ingresso do "EME 2025 – Trabalhador – Diarista R$ 65". Se continuar, essas páginas ficam (só uma de cada); se não, sai junto com o WooCommerce (item 7 do checklist) |
| 12691 | Rascunho: "Mensalidade do associado R$ 5,00" | — | Produto/rascunho da loja; ligado à decisão acima |
| 12680–12682 | Compra Direta PagSeguro – Boleto (3 páginas) | `/form-dados-do-boleto-cdps/`… | Do PagSeguro, que foi trocado pelo Mercado Pago. Provavelmente apagar |

## 4. Apagar depois do lançamento (sem utilidade)

| ID | Página | Motivo |
|---|---|---|
| 520, 521, 522, 529 | Design and Build, Kitchen Remodeling, Tiling and Painting, Hardwood Flooring | Páginas de demonstração do tema BuildPress (empresa de construção, em inglês). Estão **públicas** hoje |
| 173, 456, 463, 472 | Projects, Work In Progress, Shortcodes, Tables | Demonstração do tema BuildPress |
| 13507 | Natal das Crianças 2020 | Aviso de 2020 |
| 14550 | Retomada das palestras presenciais | Aviso de 2021 |
| 12289 | Mensagens - Palestras | Vazia |
| 10789 | Rascunho "Estudos Doutrinários" | Vazio |
| 15292 | Rascunho "Política de reembolso e devoluções" | Texto-modelo do WooCommerce |

Também sobram menus de demonstração (Extras Menu, Main Menu, Services Menu, Top Menu), que podem ser
apagados junto.

## Fora deste levantamento

- **Posts (271):** notícias e avisos antigos. Merecem um levantamento à parte.
- **Mídia:** várias imagens de 2017 não existem mais no servidor. Vale uma checagem geral da biblioteca.

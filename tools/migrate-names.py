#!/usr/bin/env python3
"""Rename the Portuguese identifiers of the old theme/plugin to English.

One-off migration helper: rewrites CSS classes, CSS custom properties,
theme.json preset slugs, block names/attributes and pattern slugs in the
given files, in place. Visitor-facing text, URLs and anchors are untouched.

Usage: tools/migrate-names.py FILE [FILE...]

Run it on page content freshly downloaded from WordPress before uploading it
back, so the pages keep working with the renamed theme and plugin. Remove this
script after the launch.
"""

import re
import sys

CLASSES = {
	"neas-abertura": "neas-hero",
	"neas-abertura-botoes": "neas-hero-buttons",
	"neas-abertura-colunas": "neas-hero-columns",
	"neas-abertura-texto": "neas-hero-text",
	"neas-abertura-titulo": "neas-hero-title",
	"neas-botao": "neas-button",
	"neas-botao-azul": "neas-button-blue",
	"neas-botao-doe": "neas-button-donate",
	"neas-cabecalho": "neas-header",
	"neas-cabecalho-linha": "neas-header-row",
	"neas-cabeca-secao": "neas-section-header",
	"neas-campos": "neas-fields",
	"neas-cartao": "neas-card",
	"neas-cartao-branco": "neas-card-white",
	"neas-cartao-post": "neas-post-card",
	"neas-cartoes": "neas-cards",
	"neas-cartoes-largos": "neas-cards-wide",
	"neas-citacao": "neas-quote",
	"neas-colunas-espacadas": "neas-columns-spaced",
	"neas-conteudo": "neas-content",
	"neas-data-cartao": "neas-date-card",
	"neas-data-dia": "neas-date-day",
	"neas-data-mes": "neas-date-month",
	"neas-data-post": "neas-post-date",
	"neas-data-semana": "neas-date-weekday",
	"neas-destaque": "neas-highlight",
	"neas-destaque-horarios": "neas-highlight-hours",
	"neas-destaque-texto": "neas-highlight-text",
	"neas-dica": "neas-hint",
	"neas-faixa-titulo": "neas-title-band",
	"neas-fundo-claro": "neas-bg-light",
	"neas-fundo-creme": "neas-bg-cream",
	"neas-grade-posts": "neas-post-grid",
	"neas-ic-coracao": "neas-ic-heart",
	"neas-ic-livro": "neas-ic-book",
	"neas-ic-palestra": "neas-ic-lecture",
	"neas-ic-pessoas": "neas-ic-people",
	"neas-icone": "neas-icon",
	"neas-imagem-post": "neas-post-image",
	"neas-instagram-reserva": "neas-instagram-placeholder",
	"neas-link-seta": "neas-arrow-link",
	"neas-lista": "neas-list",
	"neas-lista-data": "neas-list-date",
	"neas-lista-dia": "neas-list-day",
	"neas-lista-expositor": "neas-list-speaker",
	"neas-lista-item": "neas-list-item",
	"neas-lista-mes": "neas-list-month",
	"neas-lista-semana": "neas-list-weekday",
	"neas-lista-tema": "neas-list-topic",
	"neas-lista-vazia": "neas-list-empty",
	"neas-mapa": "neas-map",
	"neas-mini-cartao": "neas-mini-card",
	"neas-mini-rotulo": "neas-mini-label",
	"neas-mini-valor": "neas-mini-value",
	"neas-palestras": "neas-lectures",
	"neas-pix-botao": "neas-pix-button",
	"neas-pix-chave": "neas-pix-key",
	"neas-pix-dados": "neas-pix-details",
	"neas-pix-rotulo": "neas-pix-label",
	"neas-pix-texto": "neas-pix-text",
	"neas-previa": "neas-preview",
	"neas-previa-ativa": "neas-preview-active",
	"neas-principal": "neas-main",
	"neas-proxima": "neas-next",
	"neas-proxima-corpo": "neas-next-body",
	"neas-proxima-expositor": "neas-next-speaker",
	"neas-proxima-nota": "neas-next-note",
	"neas-proxima-rodape": "neas-next-footer",
	"neas-proxima-tema": "neas-next-topic",
	"neas-proxima-texto": "neas-next-text",
	"neas-proxima-vazia": "neas-next-empty",
	"neas-retrato": "neas-portrait",
	"neas-rodape": "neas-footer",
	"neas-rodape-colunas": "neas-footer-columns",
	"neas-rodape-final": "neas-footer-bottom",
	"neas-rodape-links": "neas-footer-links",
	"neas-rodape-nome": "neas-footer-name",
	"neas-rodape-titulo": "neas-footer-title",
	"neas-secao": "neas-section",
	"neas-secao-auta": "neas-section-auta",
	"neas-secao-instagram": "neas-section-instagram",
	"neas-secao-palestras": "neas-section-lectures",
	"neas-sobretitulo": "neas-eyebrow",
	"neas-sociais": "neas-social",
	"neas-subtitulo": "neas-subtitle",
	"neas-tabela": "neas-table",
	"neas-texto-grande": "neas-text-large",
	"neas-texto-limitado": "neas-text-narrow",
	"neas-titulo-post": "neas-post-title",
	"neas-topo": "neas-topbar",
	"neas-topo-linha": "neas-topbar-row",
	"neas-topo-texto": "neas-topbar-text",
	# Asset handles.
	"neas-blocos": "neas-site-blocks",
	"neas-funcionalidades": "neas-site",
	"neas-tema": "neas-theme",
}

# Plugin CSS custom properties (--neas-*).
CSS_VARS = {
	"azul": "blue",
	"roxo": "purple",
	"tinta": "ink",
	"texto": "text",
	"suave": "muted",
	"borda": "border",
	"titulo": "heading",
	"icone": "icon",
}

PRESETS = {
	"color": {
		"azul": "blue",
		"azul-escuro": "blue-dark",
		"roxo": "purple",
		"tinta": "ink",
		"texto": "text",
		"suave": "muted",
		"claro": "light",
		"azul-claro": "blue-light",
		"amarelo": "yellow",
		"creme": "cream",
		"borda": "border",
		"branco": "white",
	},
	"font-family": {"texto": "body", "titulos": "heading", "citacao": "quote"},
	"font-size": {
		"pequeno": "small",
		"normal": "normal",
		"medio": "medium",
		"grande": "large",
		"enorme": "x-large",
	},
}

BLOCKS = {
	"neas/proxima-palestra": "neas/next-lecture",
	"neas/proximas-palestras": "neas/upcoming-lectures",
	"neas/palestras": "neas/lecture-schedule",
	"neas/pix": "neas/pix",
	"neas/mapa": "neas/map",
}

BLOCK_ATTRIBUTES = {
	"quantidade": "count",
	"chave": "key",
	"exibir": "display",
	"endereco": "address",
}

PATTERNS = {
	"abertura": "hero",
	"atividades": "activities",
	"auta": "auta-de-souza",
	"cabecalho": "header",
	"doacoes": "donations",
	"evangelizacao": "evangelization",
	"historia": "history",
	"horarios": "opening-hours",
	"instagram": "instagram",
	"pagina-inicial": "home-page",
	"pagina-palestras": "lectures-page",
	"proximas-palestras": "upcoming-lectures",
	"rodape": "footer",
	"visite": "visit",
}

TEMPLATES = {"pagina-inteira": "full-width"}


def migrate(text):
	text = re.sub(
		r"(?<![\w-])neas-[a-z0-9-]*[a-z0-9]",
		lambda m: CLASSES.get(m.group(0), m.group(0)),
		text,
	)
	text = re.sub(
		r"--neas-([a-z-]+)",
		lambda m: "--neas-" + CSS_VARS.get(m.group(1), m.group(1)),
		text,
	)
	for kind, slugs in PRESETS.items():
		text = re.sub(
			r"(--wp--preset--%s--|var:preset\|%s\|)([a-z0-9-]*[a-z0-9])" % (kind, kind),
			lambda m, slugs=slugs: m.group(1) + slugs.get(m.group(2), m.group(2)),
			text,
		)

	def block(m):
		name = BLOCKS[m.group(2)]
		attributes = m.group(3) or ""
		for old, new in BLOCK_ATTRIBUTES.items():
			attributes = attributes.replace('"%s":' % old, '"%s":' % new)
		return m.group(1) + name + attributes

	text = re.sub(
		r"(<!-- wp:)(%s)(?![\w-])( \{[^}]*\})?"
		% "|".join(re.escape(b) for b in sorted(BLOCKS, key=len, reverse=True)),
		block,
		text,
	)
	text = re.sub(
		r'("slug":"neas/|Slug: neas/)([a-z-]+)',
		lambda m: m.group(1) + PATTERNS.get(m.group(2), m.group(2)),
		text,
	)
	text = re.sub(
		r'("template":")([a-z-]+)',
		lambda m: m.group(1) + TEMPLATES.get(m.group(2), m.group(2)),
		text,
	)
	return text


if __name__ == "__main__":
	for path in sys.argv[1:]:
		with open(path, encoding="utf-8") as f:
			original = f.read()
		migrated = migrate(original)
		if migrated != original:
			with open(path, "w", encoding="utf-8") as f:
				f.write(migrated)
			print("migrated", path)

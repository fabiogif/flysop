# -*- coding: utf-8 -*-
from pathlib import Path
import html as H
import re

ROOT = Path(r"C:/Users/fabio/Documents/projetos/robson/flysop/flysop")
md = (ROOT / "guia-tecnico-aplicacao.md").read_text(encoding="utf-8")
lines = md.splitlines()
out = []
out.append("""<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"/>
<title>Guia Técnico — flySOP</title>
<style>
@page{size:A4;margin:16mm 14mm 18mm 14mm}
body{font-family:Segoe UI,Arial,sans-serif;font-size:10.5pt;line-height:1.45;color:#0f172a;margin:0}
h1{font-size:18pt;color:#155e75}
h2{font-size:13.5pt;color:#155e75;border-bottom:2px solid #0e7490;padding-bottom:4px;margin-top:22px;page-break-after:avoid}
h3{font-size:11.5pt;color:#0e7490;margin-top:14px;page-break-after:avoid}
p,li{orphans:3;widows:3}
code{font-family:Consolas,monospace;font-size:9pt;background:#f8fafc;padding:1px 3px;border-radius:3px}
pre{background:#0f172a;color:#e2e8f0;padding:10px;border-radius:8px;font-size:8.5pt;white-space:pre-wrap;page-break-inside:avoid}
table{width:100%;border-collapse:collapse;margin:8px 0 14px;font-size:8.8pt;page-break-inside:avoid}
th,td{border:1px solid #e2e8f0;padding:5px 6px;vertical-align:top;text-align:left}
th{background:#f1f5f9;color:#155e75}
blockquote{border-left:4px solid #0e7490;background:#f8fafc;margin:10px 0;padding:8px 12px}
hr{border:none;border-top:1px solid #e2e8f0;margin:18px 0}
.cover{min-height:88vh;padding:28px;background:linear-gradient(165deg,#f8fafc,#ecfeff 55%,#fff);border:1px solid #e2e8f0;page-break-after:always;display:flex;flex-direction:column;justify-content:center}
.eyebrow{text-transform:uppercase;letter-spacing:.12em;color:#0e7490;font-weight:700;font-size:9.5pt}
</style></head><body>
<section class="cover">
<div class="eyebrow">Documento destinado à Banca Examinadora</div>
<h1>flySOP / SOPADMIN</h1>
<p style="font-size:15pt;font-weight:600;color:#0e7490">Guia Técnico da Aplicação</p>
<p>Análise do código-fonte e confronto com o plano <em>virtual-jingling-cookie.md</em>.</p>
<p><strong>Versão:</strong> 1.0 &nbsp;|&nbsp; <strong>Data:</strong> 10/08/2026</p>
<p><strong>Stack:</strong> Laravel 8.83 · PHP 8.1 · PostgreSQL · Sanctum · AdminLTE · Vue 2</p>
</section>
""")

in_code = False
para = []


def flush_para():
    global para
    if para:
        out.append("<p>" + " ".join(para) + "</p>")
        para = []


def inline(s: str) -> str:
    s = H.escape(s)
    s = re.sub(r"\*\*(.+?)\*\*", r"<strong>\1</strong>", s)
    s = re.sub(r"(?<!\*)\*(.+?)\*(?!\*)", r"<em>\1</em>", s)
    s = re.sub(r"`([^`]+)`", r"<code>\1</code>", s)
    return s


i = 0
skip_first_h1 = True
while i < len(lines):
    line = lines[i]
    if line.startswith("```"):
        flush_para()
        if not in_code:
            in_code = True
            out.append("<pre>")
        else:
            in_code = False
            out.append("</pre>")
        i += 1
        continue
    if in_code:
        out.append(H.escape(line) + "\n")
        i += 1
        continue
    if line.startswith("|") and "|" in line[1:]:
        flush_para()
        rows = []
        while i < len(lines) and lines[i].startswith("|"):
            rows.append(lines[i])
            i += 1
        html_rows = []
        first = True
        for r in rows:
            cells = [c.strip() for c in r.strip("|").split("|")]
            if all(set(c) <= set("-: ") for c in cells):
                continue
            tag = "th" if first else "td"
            html_rows.append(
                "<tr>"
                + "".join(f"<{tag}>" + inline(c) + f"</{tag}>" for c in cells)
                + "</tr>"
            )
            first = False
        out.append("<table>" + "".join(html_rows) + "</table>")
        continue
    if line.startswith("# "):
        flush_para()
        if skip_first_h1:
            skip_first_h1 = False
            i += 1
            continue
        out.append("<h1>" + inline(line[2:]) + "</h1>")
        i += 1
        continue
    if line.startswith("## "):
        flush_para()
        out.append("<h2>" + inline(line[3:]) + "</h2>")
        i += 1
        continue
    if line.startswith("### "):
        flush_para()
        out.append("<h3>" + inline(line[4:]) + "</h3>")
        i += 1
        continue
    if line.strip() == "---":
        flush_para()
        out.append("<hr/>")
        i += 1
        continue
    if line.startswith("> "):
        flush_para()
        out.append("<blockquote>" + inline(line[2:]) + "</blockquote>")
        i += 1
        continue
    if line.startswith("- "):
        flush_para()
        out.append("<ul>")
        while i < len(lines) and lines[i].startswith("- "):
            out.append("<li>" + inline(lines[i][2:]) + "</li>")
            i += 1
        out.append("</ul>")
        continue
    if line.strip() == "":
        flush_para()
        i += 1
        continue
    para.append(inline(line))
    i += 1

flush_para()
out.append(
    '<p style="margin-top:24px;font-size:9pt;color:#64748b;border-top:1px solid #e2e8f0;padding-top:8px">'
    "Documento gerado por análise do código-fonte. Não contém secrets. Versão 1.0 — 10/08/2026."
    "</p></body></html>"
)
(ROOT / "guia-tecnico-aplicacao.html").write_text("".join(out), encoding="utf-8")
print("OK", (ROOT / "guia-tecnico-aplicacao.html").stat().st_size)

import pathlib

DOSSIER_PATH = pathlib.Path("docs/CLMS_Technical_Dossier.md")
DDL_PATH = pathlib.Path("scripts/extracted_ddl.sql")

marker = "## Appendix A — Complete DDL (MySQL 9.1.0)"

dossier_text = DOSSIER_PATH.read_text(encoding="utf-8")
ddl_text = DDL_PATH.read_text(encoding="utf-8").rstrip("\n")

marker_index = dossier_text.find(marker)
if marker_index == -1:
    raise SystemExit("Marker not found in dossier.")

marker_line_end = dossier_text.find("\n", marker_index)
if marker_line_end == -1:
    marker_line_end = len(dossier_text)

new_text = dossier_text[:marker_line_end] + "\n\n```sql\n" + ddl_text + "\n```\n"

DOSSIER_PATH.write_text(new_text, encoding="utf-8")

print("Updated appendix with fenced SQL DDL.")






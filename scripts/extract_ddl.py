import pathlib
import re

SQL_PATH = pathlib.Path("DB_DUMP") / "litigation_db_ver2 (23Oct2025-650PM).sql"

pattern = re.compile(r"CREATE TABLE IF NOT EXISTS `(?P<table>[^`]+)` \((?P<body>.*?)\) ENGINE=", re.S)

sql_text = SQL_PATH.read_text(encoding="utf-8")

ddl_statements = []
markdown_sections = []

for match in pattern.finditer(sql_text):
    table = match.group("table")
    body = match.group("body").rstrip()

    ddl_statements.append(f"-- {table}")
    ddl_statements.append(body)
    ddl_statements.append(");")
    ddl_statements.append("")

    lines = [line.strip() for line in body.splitlines() if line.strip()]
    columns = []
    indexes = []

    for line in lines:
        if line.startswith("`"):
            col_match = re.match(
                r"`(?P<name>[^`]+)`\s+(?P<type>[^\s]+(?:\([^\)]*\))?)(?P<rest>.*)",
                line.rstrip(","),
            )
            if not col_match:
                continue
            name = col_match.group("name")
            col_type = col_match.group("type")
            rest = col_match.group("rest") or ""
            nullable = "NO" if "NOT NULL" in rest.upper() else "YES"
            default_match = re.search(r"DEFAULT\s+([^,\s]+)", rest, re.IGNORECASE)
            default = default_match.group(1) if default_match else "—"
            extra_parts = []
            if "AUTO_INCREMENT" in rest.upper():
                extra_parts.append("AUTO_INCREMENT")
            if "UNSIGNED" in col_type.upper():
                extra_parts.append("UNSIGNED")
            if "COMMENT" in rest.upper():
                comment = rest.split("COMMENT", 1)[1].strip().strip("'\"")
                extra_parts.append(f"COMMENT {comment}")
            extra = ", ".join(extra_parts) if extra_parts else "—"

            columns.append((name, col_type, nullable, default, extra))
        elif line.upper().startswith("PRIMARY KEY") or line.upper().startswith("UNIQUE KEY") or line.upper().startswith("KEY"):
            indexes.append(line.rstrip(","))

    markdown_sections.append(f"### `{table}`")
    markdown_sections.append("| Column | DB Type | Null | Default | Extra |")
    markdown_sections.append("|---|---|---|---|---|")
    for name, col_type, nullable, default, extra in columns:
        markdown_sections.append(f"| `{name}` | {col_type} | {nullable} | {default} | {extra} |")
    if indexes:
        markdown_sections.append("")
        markdown_sections.append("**Indexes:**")
        for idx in indexes:
            markdown_sections.append(f"- `{idx}`")
    markdown_sections.append("")

ddl_output = "\n".join(ddl_statements)
markdown_output = "\n".join(markdown_sections)

pathlib.Path("scripts/extracted_ddl.sql").write_text(ddl_output, encoding="utf-8")
pathlib.Path("scripts/domain_tables.md").write_text(markdown_output, encoding="utf-8")

print("Extracted DDL to scripts/extracted_ddl.sql")
print("Generated Markdown table overview at scripts/domain_tables.md")


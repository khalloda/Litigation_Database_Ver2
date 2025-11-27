import pathlib
import re

SQL_PATH = pathlib.Path("DB_DUMP") / "litigation_db_ver2 (23Oct2025-650PM).sql"

sql_text = SQL_PATH.read_text(encoding="utf-8")

pattern = re.compile(r"INSERT INTO `option_sets`.*?VALUES\s*(.*?);", re.S)

option_sets = set()

for match in pattern.finditer(sql_text):
    chunk = match.group(1)
    rows = chunk.split("),")
    for row in rows:
        stripped = row.strip()
        if not stripped:
            continue
        tup_match = re.search(r"\((?:\d+|NULL),\s*'([^']*)',\s*'([^']*)',\s*'([^']*)'", stripped)
        if tup_match:
            option_sets.add(tup_match.groups())

output_path = pathlib.Path("scripts/option_sets.txt")
with output_path.open("w", encoding="utf-8") as handle:
    handle.write(f"Found {len(option_sets)} option sets\n")
    for key, name_en, name_ar in sorted(option_sets):
        handle.write(f"{key} | {name_en} | {name_ar}\n")

print(f"Wrote option set summary to {output_path}")


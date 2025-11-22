import pathlib
import re
from collections import defaultdict

SQL_PATH = pathlib.Path("DB_DUMP") / "litigation_db_ver2 (23Oct2025-650PM).sql"

sql_text = SQL_PATH.read_text(encoding="utf-8")

set_pattern = re.compile(r"INSERT INTO `option_sets`.*?VALUES\s*(.*?);", re.S)
value_pattern = re.compile(r"INSERT INTO `option_values`.*?VALUES\s*(.*?);", re.S)

set_rows = []
for match in set_pattern.finditer(sql_text):
    chunk = match.group(1)
    set_rows.extend(chunk.split("),"))

set_map: dict[int, tuple[str, str, str]] = {}
for row in set_rows:
    stripped = row.strip()
    if not stripped:
        continue
    tup_match = re.search(
        r"\((?:\d+|NULL),\s*'([^']*)',\s*'([^']*)',\s*'([^']*)',\s*'([^']*)',\s*'([^']*)',\s*(\d)",
        stripped,
    )
    if tup_match:
        key, name_en, name_ar, desc_en, desc_ar, is_active = tup_match.groups()
        # Extract ID separately (first integer)
        id_match = re.match(r"\((\d+)", stripped)
        if id_match:
            set_id = int(id_match.group(1))
            set_map[set_id] = (key, name_en, name_ar, desc_en, desc_ar, int(is_active))

value_rows = []
for match in value_pattern.finditer(sql_text):
    chunk = match.group(1)
    value_rows.extend(chunk.split("),"))

summary = defaultdict(list)
for row in value_rows:
    stripped = row.strip()
    if not stripped:
        continue
    tup_match = re.search(
        r"\((?:\d+|NULL),\s*(\d+),\s*'([^']*)',\s*'([^']*)',\s*'([^']*)',\s*(\d+),\s*(\d)",
        stripped,
    )
    if tup_match:
        set_id, code, label_en, label_ar, position, is_active = tup_match.groups()
        set_id_int = int(set_id)
        summary[set_id_int].append(
            {
                "code": code,
                "label_en": label_en,
                "label_ar": label_ar,
                "position": int(position),
                "is_active": int(is_active),
            }
        )

output_path = pathlib.Path("scripts/option_values_summary.txt")
with output_path.open("w", encoding="utf-8") as handle:
    for set_id, values in sorted(summary.items()):
        key_meta = set_map.get(set_id, ("unknown", "", "", "", "", 1))
        key, name_en, name_ar, desc_en, desc_ar, is_active = key_meta
        values_sorted = sorted(values, key=lambda item: item["position"])
        handle.write(f"[{set_id}] {key} — {name_en} / {name_ar} (active={is_active})\n")
        handle.write(f"Description EN: {desc_en}\n")
        handle.write(f"Description AR: {desc_ar}\n")
        handle.write(f"Total values: {len(values_sorted)}\n")
        sample = values_sorted[:5]
        for val in sample:
            handle.write(
                f"  - pos {val['position']:02d} | code={val['code']} | {val['label_en']} / {val['label_ar']} (active={val['is_active']})\n"
            )
        if len(values_sorted) > 5:
            handle.write("  ...\n")
        handle.write("\n")

print(f"Wrote option value summary to {output_path}")







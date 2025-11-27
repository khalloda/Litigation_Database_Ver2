import re, random, sys, os

def parse_dump(file_path):
    table_data = {}
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()
    # Find all INSERT statements
    inserts = re.findall(r"INSERT INTO `([^`]+)` VALUES (.+?);", content, flags=re.DOTALL)
    for table, values_blob in inserts:
        # Split rows: each row is enclosed in parentheses and separated by '),('
        rows = re.findall(r"\(([^)]+)\)", values_blob)
        for row in rows:
            # Simple split on commas to get first column (id). This works for numeric ids.
            cols = [c.strip() for c in row.split(',')]
            if not cols:
                continue
            pk = cols[0]
            # Store the raw row string for later comparison (remove surrounding whitespace)
            table_data.setdefault(table, {})[pk] = row.strip()
    return table_data

def sample_and_compare(orig_path, new_path, sample_size=20):
    orig = parse_dump(orig_path)
    new = parse_dump(new_path)
    for table, orig_rows in orig.items():
        ids = list(orig_rows.keys())
        if not ids:
            print(f"{table}: no records")
            continue
        sample_ids = random.sample(ids, min(sample_size, len(ids)))
        match = 0
        mismatches = []
        for pk in sample_ids:
            if pk not in new.get(table, {}):
                mismatches.append((pk, 'missing'))
                continue
            if orig_rows[pk] != new[table][pk]:
                mismatches.append((pk, 'different'))
                continue
            match += 1
        print(f"{table}: {match}/{len(sample_ids)} records match")
        for pk, reason in mismatches:
            print(f"  ID {pk} -> {reason}")

if __name__ == '__main__':
    if len(sys.argv) != 3:
        print('Usage: python verify_integrity.py <original_dump.sql> <new_dump.sql>')
        sys.exit(1)
    orig_path = sys.argv[1]
    new_path = sys.argv[2]
    if not os.path.isfile(orig_path) or not os.path.isfile(new_path):
        print('One of the dump files does not exist')
        sys.exit(1)
    sample_and_compare(orig_path, new_path)

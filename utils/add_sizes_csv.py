import csv 
with open('forStmt.csv', mode='r') as csv_file:
    reader = csv.DictReader(csv_file)

    with open('size_insert.txt', mode='w') as txt_file:
        for row in reader:
            product_id = row['product_id']
            size_id = row['size_id']

            insert_statement = f"INSERT into products_sizes_new (product_id, size_id) VALUES ('{product_id}', '{size_id}');\n"

            txt_file.write(insert_statement)

print("SQL statements written to size_insert.txt")
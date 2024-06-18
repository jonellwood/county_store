import csv 
with open('forStmt.csv', mode='r') as csv_file:
    reader = csv.DictReader(csv_file)

    with open('product_insert.txt', mode='w') as txt_file:
        for row in reader:
            product_id = row['product_id']
            size_id = row['size_id']
            price = row['price']

            insert_statement = f"INSERT into PRICES (product_id, vendor_id, size_id, price) VALUES ('{product_id}', 1, '{size_id}', '{price}');\n"

            txt_file.write(insert_statement)

print("SQL statements written to product_insert.txt")
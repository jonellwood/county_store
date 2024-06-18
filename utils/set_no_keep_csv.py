import csv

p_id=[]

with open("setNotKeep.csv", "r") as csvfile:
    reader = csv.reader(csvfile)

    with open('setNoKeepSql.txt', mode='w') as txt_file:
        for row in reader:
            product_id = row[0]

            if product_id not in p_id:
                p_id.append(product_id)

                insert_statement = f"UPDATE products_new SET keep = 0 where product_id = '{product_id}';\n"

                txt_file.write(insert_statement)
print(p_id)
print("SQL Statements should be ready sir.")



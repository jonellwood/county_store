import csv 
def update_product_keep(csv_file, output_file):
  """
  Reads a CSV file and writes update statements to a text file if 'keep' is 0.

  Args:
    csv_file (str): Path to the CSV file (products_to_remove.csv).
    output_file (str): Path to the output text file (product_mark_NO_keep.txt).
  """

  with open(csv_file, 'r') as csvfile, open(output_file, 'w') as outfile:
    reader = csv.DictReader(csvfile)
    for row in reader:
      try:
        if int(row['keep']) == 0:
            product_id = row['product_id']
            update_query = f"UPDATE products_new SET keep = 0 WHERE product_id = {product_id};\n"
            outfile.write(update_query)
      except ValueError:
        pass
    
if __name__ == "__main__":
  csv_file = "products_to_remove.csv"
  output_file = "product_mark_NO_keep.txt"
  update_product_keep(csv_file, output_file)
  print(f"Update statements written to: {output_file}")
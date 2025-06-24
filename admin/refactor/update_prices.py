import csv

# Define the input and output file names
input_file = 'prices_updated_magic.csv'
output_file = 'price_update_statements.txt'

# Open the CSV file and the output text file
with open(input_file, mode='r', encoding='utf-8-sig') as file:
    reader = csv.DictReader(file)
    
    # Normalize the fieldnames to remove leading/trailing whitespace and lowercase them
    reader.fieldnames = [field.strip().lower() for field in reader.fieldnames]
    
    # Debug: Print the normalized field names to check for correctness
    print("Normalized CSV Headers:", reader.fieldnames)
    
    with open(output_file, mode='w') as output:
        # Iterate through each row in the CSV
        for row in reader:
            # Normalize the keys of the row dictionary
            row = {k.strip().lower(): v for k, v in row.items()}
            
            # Debug: Print the current row to inspect its contents
            print("Current Row:", row)
            
            try:
                # Check if addAmt is greater than 0
                if float(row['addamt']) > 0:
                    # Generate the update statement
                    new_price = float(row['newprice'])
                    price_id = row['price_id']
                    update_statement = f"UPDATE prices SET price = {new_price} WHERE price_id = {price_id};\n"
                    
                    # Write the update statement to the text file
                    output.write(update_statement)
            except KeyError as e:
                print(f"KeyError: {e}. Please check the CSV headers and the script for matching column names.")
            except ValueError as e:
                print(f"ValueError: {e}. Please check the data types of the 'addamt' and 'newprice' columns.")


import csv
import os
import shutil
import re

# Define the file paths
csv_file = 'product_codes.csv'
product_images_folder = 'product-images'
unused_product_images_folder = 'unused-product-images'

# Create the 'unused-product-images' folder if it doesn't exist
os.makedirs(unused_product_images_folder, exist_ok=True)

# Read product codes from the CSV file and convert them to lowercase
with open(csv_file, 'r') as file:
    reader = csv.reader(file)
    product_codes = [row[0].lower() for row in reader]

# Loop through the 'product-images' folder
for filename in os.listdir(product_images_folder):
    lowercase_filename = filename.lower()
    for code in product_codes:
        if not re.search(r'\b' + re.escape(code) + r'\b', lowercase_filename):
            # Move the image file to 'unused-product-images' folder
            src = os.path.join(product_images_folder, filename)
            dst = os.path.join(unused_product_images_folder, filename)
            shutil.move(src, dst)
            break  # Move to the next image file

print("Images moved successfully.")
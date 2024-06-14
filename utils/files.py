from open_links import open_links_from_text_file
import os

def process_text_files_in_directory(directory):
    print('Charming the 🐍')
    print('Reading the file hisssssstory');
    # Iterate over all files in the specified directory
    for filename in os.listdir(directory):
        # Check if the file is a text file
        if filename.endswith(".txt"):
            # Construct the full path to the text file
            file_path = os.path.join(directory, filename)
            # Get the product number from the filename (assuming the format is 'product_number.txt')
            product_number = os.path.splitext(filename)[0]
            # Construct the directory path with product number
            output_directory = os.path.join(directory, product_number)
            print(output_directory)
            # Call open_links_from_text_file for each text file with the new directory path
            open_links_from_text_file(file_path)


directory_path = './out'
process_text_files_in_directory(directory_path)

# def process_text_files_in_directory(directory):
#     # Iterate over all files in the specified directory
#     for filename in os.listdir(directory):
#         # Check if the file is a text file
#         if filename.endswith(".txt"):
#             # Construct the full path to the text file
#             file_path = os.path.join(directory, filename)
#             # Call open_links_from_text_file for each text file
#             open_links_from_text_file(file_path)

# directory_path = './out'
# process_text_files_in_directory(directory_path)



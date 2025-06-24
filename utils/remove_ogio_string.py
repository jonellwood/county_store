import os

def remove_ogio_from_gif_files(folder_path):
    for filename in os.listdir(folder_path):
        if filename.endswith('.gif') and filename.startswith('ogio-'):
            parts = filename.split('-')
            new_filename = '-'.join(parts[2:]).strip()  # Extracts the part after the second hyphen
            new_filepath = os.path.join(folder_path, new_filename) + '.gif'  # Append file extension to new filename
            try:
                os.rename(os.path.join(folder_path, filename), new_filepath)
                print(f"Removed 'ogio' prefix: {filename} -> {new_filename}")
            except Exception as e:
                print(f"Error renaming file: {filename} -> {new_filename}. Error: {e}")


# folder_path = './images_down/'
# remove_ogio_from_gif_files(folder_path)


# def remove_ogio_from_gif_files(folder_path):
#     for filename in os.listdir(folder_path):
#         if filename.endswith('.gif') and filename.startswith('ogio-'):
#             parts = filename.split('-')
#             new_filename = '-'.join(parts[2:]).strip()  # Extracts the part after the second hyphen
#             os.rename(os.path.join(folder_path, filename), os.path.join(folder_path, new_filename))
#             print(f"Remove ogio renamed: {filename} to {new_filename}")

# Specify the folder path where the files are located
# folder_path = './images_down'

# Call the function to remove the prefix from .gif files in the specified folder
# remove_ogio_from_gif_files(folder_path)
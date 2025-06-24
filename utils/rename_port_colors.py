import os

def remove_prefix_from_gif_files(folder_path):
    for filename in os.listdir(folder_path):
        if filename.endswith('.gif') and filename.startswith('port-'):
            new_filename = filename.replace('port-', '', 1)
            os.rename(os.path.join(folder_path, filename), os.path.join(folder_path, new_filename))
            print(f"Rename port colors renamed: {filename} to {new_filename}")

# Specify the folder path where the files are located
# folder_path = './images_down'

# Call the function to remove the prefix from .gif files in the specified folder
# remove_prefix_from_gif_files(folder_path)

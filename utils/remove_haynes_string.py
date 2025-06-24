import os

def remove_haynes_from_gif_files(folder_path):
    for filename in os.listdir(folder_path):
        if filename.endswith('.gif') and filename.startswith('hanes-'):
            new_filename = filename.replace('hanes-', '', 1)
            os.rename(os.path.join(folder_path, filename), os.path.join(folder_path, new_filename))
            print(f"Remove Hanes renamed: {filename} to {new_filename}")

# Specify the folder path where the files are located
# folder_path = './images_down'

# Call the function to remove the prefix from .gif files in the specified folder
# remove_haynes_from_gif_files(folder_path)
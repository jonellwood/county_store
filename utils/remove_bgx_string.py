import os

def remove_bg_prefix_from_gif_files(folder_path):
    for filename in os.listdir(folder_path):
        if filename.endswith('.gif') and filename.startswith('bg'):
            # Find the index of the first '-' after 'PC'
            dash_index = filename.find('-', 2)
            if dash_index != -1:  # If '-' is found after 'PC'
                new_filename = filename[dash_index + 1:]  # Extract the part after '-'
                os.rename(os.path.join(folder_path, filename), os.path.join(folder_path, new_filename))
                print(f"Remove bgx renamed: {filename} to {new_filename}")

# Specify the folder path where the files are located
# folder_path = './images_down'

# Call the function to remove the prefix from .gif files in the specified folder
# remove_st_from_gif_files(folder_path)
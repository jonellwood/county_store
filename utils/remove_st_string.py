import os

# def remove_st_from_gif_files(folder_path):
#     for filename in os.listdir(folder_path):
#         if filename.endswith('.gif') and filename.startswith('ST-'):
#             new_filename = filename.replace('ST-', '', 1)
#             os.rename(os.path.join(folder_path, filename), os.path.join(folder_path, new_filename))
#             print(f"Renamed file: {filename} to {new_filename}")


def remove_st_from_gif_files(folder_path):
    for filename in os.listdir(folder_path):
        if filename.endswith('.gif') and filename.startswith('st'):
            # Find the index of the first '-' after 'ST'
            dash_index = filename.find('-', 2)
            if dash_index != -1:  # If '-' is found after 'ST'
                new_filename = filename[dash_index + 1:]  # Extract the part after '-'
                os.rename(os.path.join(folder_path, filename), os.path.join(folder_path, new_filename))
                print(f"Renamed st renamed: {filename} to {new_filename}")
# Specify the folder path where the files are located
# folder_path = './images_down'

# Call the function to remove the prefix from .gif files in the specified folder
# remove_st_from_gif_files(folder_path)
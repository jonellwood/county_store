import os

def remove_portco_from_gif_files(folder_path):
    for filename in os.listdir(folder_path):
        if filename.endswith('.gif') and (filename.startswith('portandco-') or filename.startswith('portco-')):
            if filename.startswith('portandco-'):
                prefix_length = len('portandco-')
            else:
                prefix_length = len('portco-')
            new_filename = filename[prefix_length:]
            os.rename(os.path.join(folder_path, filename), os.path.join(folder_path, new_filename))
            print(f"Remove portco renamed: {filename} to {new_filename}")

# Specify the folder path where the files are located
# folder_path = './images_down'

# Call the function to remove the prefix from .gif files in the specified folder
# remove_portco_from_gif_files(folder_path)
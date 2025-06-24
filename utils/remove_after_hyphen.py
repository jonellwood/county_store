import os 

def remove_after_hyphen(folder_path):
    for filename in os.listdir(folder_path):
        if filename.endswith('.gif') and '-' in filename:
            parts = filename.split('-')
            new_filename = parts[0] + '.gif'
            if new_filename != filename:
                os.rename(os.path.join(folder_path, filename), os.path.join(folder_path, new_filename))
                print(f"Remove after hyphen renamed: {filename} to {new_filename}")

# folder_path = './images_down'
# remove_after_hyphen(folder_path)
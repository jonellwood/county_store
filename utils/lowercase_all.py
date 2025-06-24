import os 

def lowercase_all(folder_path):
    print(folder_path)
    for filename in os.listdir(folder_path):
        new_filename = filename.lower()
        os.rename(os.path.join(folder_path, filename), os.path.join(folder_path, new_filename))
        print(f"Made: {filename} look like {new_filename}")

lowercase_all('../product-images')
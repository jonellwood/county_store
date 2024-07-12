import os

folder_path = 'temp'

for filename in os.listdir(folder_path):
    if filename.endswith('.jpg') and '_' in filename:
        index = filename.index('_') + 1
        new_filename = filename[index:]
        old_path = os.path.join(folder_path, filename)
        new_path = os.path.join(folder_path, new_filename)
        os.rename(old_path, new_path)
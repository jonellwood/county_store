import os
import shutil

def move_copied_files(source_folder, destination_folder, copied_files):
    # Create the destination folder if it doesn't exist
    if not os.path.exists(destination_folder):
        os.makedirs(destination_folder)
    
    # Initialize a counter for the number of files moved
    num_files_moved = 0
    
    # Iterate over the files in the source folder
    for filename in os.listdir(source_folder):
        # Check if the file was copied
        if filename in copied_files:
            source_file_path = os.path.join(source_folder, filename)
            destination_file_path = os.path.join(destination_folder, filename)
            
            # Move the file to the destination folder
            shutil.move(source_file_path, destination_file_path)
            print(f"Moved file: {filename}")
            num_files_moved += 1  # Increment the counter
    
    # Print the total number of files moved
    print(f"Total number of files moved: {num_files_moved}")

# Paths to the folders
source_folder = './images_down'
destination_folder = './images_moved'

# List of copied files (assuming you saved this list)
# copied_files = [...]  # Replace [...] with the list of copied files

# Move the copied files from './images_down' to './images_moved'
# move_copied_files(source_folder, destination_folder, copied_files)

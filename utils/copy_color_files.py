import os
import shutil

def compare_folders(source_folder, destination_folder):
    # Get list for source
    files_in_source = set(os.listdir(source_folder))
    
    # Get list for dest
    files_in_dest = set(os.listdir(destination_folder))
    
    # Find the files that are in the 1 but not 2
    files_to_copy = set(files_in_source) - set(files_in_dest)
    
    num_files_copied = 0
    # print(files_to_copy)
    for filename in files_to_copy:
        source_file_path = os.path.join(source_folder, filename)
        destination_file_path = os.path.join(destination_folder, filename)

        if filename.endswith('.gif') and len(filename) > 2 and filename != '.gif':
            shutil.copyfile(source_file_path, destination_file_path)
            print(f"Copied file: {filename}")
            num_files_copied += 1
    
    print(f"Total number of files copied: {num_files_copied}")
    move_copied_files(source_folder, final_destination_folder)

    for filename in os.listdir(source_folder):
        if filename.endswith('.gif') and len(filename) > 2 and filename != '.gif':
            source_file_path = os.path.join(source_folder, filename)
            os.remove(source_file_path)
            print(f"Deleting:  {filename}")
    print("Now run add_image_links.py")
# PFolder paths
source_folder = './images_down'
destination_folder = '../color-images'
final_destination_folder = './images_moved'

def move_copied_files(source_folder, final_destination_folder):
    # Create the destination folder if it doesn't exist
    if not os.path.exists(final_destination_folder):
        os.makedirs(final_destination_folder)
    
    # Initialize a counter for the number of files moved
    num_files_moved = 0
    files = set(source_folder)
    # Iterate over the files in the source folder
    for filename in os.listdir(source_folder):
        # Check if the file was copied
        if filename in files:
            source_file_path = os.path.join(source_folder, filename)
            destination_file_path = os.path.join(final_destination_folder, filename)
            
            # Move the file to the destination folder
            shutil.move(source_file_path, destination_file_path)
            print(f"Moved file: {filename}")
            num_files_moved += 1  # Increment the counter
    
    # Print the total number of files moved
    print(f"Total number of files moved: {num_files_moved}")
    print("Now run add_image_links.py")


# Compare & Copy
compare_folders(source_folder, destination_folder)
import os

def remove_sm(holding_folder, move_to_folder):
    for filename in os.listdir(holding_folder):
        if filename.endswith(".jpg"):
            new_filename = filename.replace("sm-", "", 1)  # Replace only the first occurrence
            if new_filename != filename:  # Only rename if there was a change
                old_filepath = os.path.join(holding_folder, filename)
                new_filepath = os.path.join(move_to_folder, new_filename)
                os.rename(old_filepath, new_filepath)
                print(f"Renamed {filename} to {new_filename}")

# Example usage
holding_folder = "./holding"
move_to_folder = '../product-images'
remove_sm(holding_folder, move_to_folder)

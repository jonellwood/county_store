import os
import requests
import re
import sys
import datetime

# Function to extract colors from text files
def extract_colors_from_file(file_path):
    colors = []
    with open(file_path, 'r') as file:
        lines = file.readlines()
        for line in lines:
            if line.startswith('+'):
                colors.append(line.strip('- ').strip())
    return colors

def insert_space_before_slash(color):
    # return color.replace('/', ' / ')
    return re.sub(r'\s*/\s*', ' / ', color)
# Main function to process text files and send POST requests
def process_text_files(directory):
    timestamp = datetime.datetime.now().strftime("%Y-%m-%d_%H-%M-%S")
    log_file_name = f"color_insert_log_{timestamp}.txt"
    log_file_path = os.path.join(log_directory_path, log_file_name)
    with open(log_file_path, 'w') as log_file:

        # file_path = './insert_logs'

        sys.stdout = log_file
        print(f"Starting the shiz")
        for filename in os.listdir(directory):
            if filename.endswith(".processed"):
                colors = extract_colors_from_file(os.path.join(directory, filename))
                # print(colors)
                for color in colors:
                    # new_color = color.replace('+ ', '', 1)
                    new_color = insert_space_before_slash(color).replace('+ ', '', 1)
                    print(f"Renamed file: {color} to {new_color}")
                    print(f"Checking Database  🤖 🤖 🤖 🤖 ✔")
                    # payload = {'color': color}
                    # print(new_color)
                    response = requests.post('https://store.berkeleycountysc.gov/add-color-database.php', params={'colorName': new_color})
                    if response.status_code == 200:
                        print(f"Color '{new_color}' added successfully.")
                    else:
                        print(f"Failed to add color '{new_color}'.")
        sys.stdout = sys.__stdout__


# Directory containing text files
directory_path = './out'
log_directory_path = './insert_logs'  # Specify the log directory path
os.makedirs(log_directory_path, exist_ok=True)  # Create the log directory if it doesn't exist
process_text_files(directory_path)


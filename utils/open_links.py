# import webbrowser

# def open_links_from_text_file(text_file_path):
#     # Function to extract URLs from lines containing links
#     def extract_urls(line):
#         urls = []
#         # Split the line by whitespace and iterate over each word
#         for word in line.split():
#             # Check if the word starts with "http://" or "https://"
#             if word.startswith("http://") or word.startswith("https://"):
#                 urls.append(word)
#         return urls

#     # Open the text file and iterate over each line
#     with open(text_file_path, "r") as file:
#         for line in file:
#             # Check if the line contains the relevant information (spec sheet, main image, swatch links)
#             if "Spec Sheet:" in line:
#                 spec_sheet_url = extract_urls(line)[0]  # Extract the first URL from the line
#                 webbrowser.open(spec_sheet_url)
#             elif "Main Image:" in line:
#                 main_image_url = extract_urls(line)[0]  # Extract the first URL from the line
#                 webbrowser.open(main_image_url)
#             elif "Swatch Links:" in line:
#                 # Extract all URLs from the following lines until a line break or the end of the file
#                 for next_line in file:
#                     if next_line.strip():  # Check if the line is not empty
#                         swatch_urls = extract_urls(next_line)
#                         for url in swatch_urls:
#                             webbrowser.open(url)
#                     else:
#                         break  # Exit the loop if a line break is encountered

# ATTEMPTING TO ADD AUTO DOWNLOAD FUNCTION

import os
import webbrowser
import requests
from urllib.parse import urlparse

def open_links_from_text_file(text_file_path):
    # Function to extract URLs from lines containing links
    def extract_urls(line):
        urls = []
        # Split the line by whitespace and iterate over each word
        for word in line.split():
            # Check if the word starts with "http://" or "https://"
            if word.startswith("http://") or word.startswith("https://"):
                urls.append(word)
        return urls

    # Function to save image from URL
    def save_image_from_url(url, folder_path):
        # Extract filename from URL
        filename = os.path.basename(urlparse(url).path)
        # Save image to specified folder
        with open(os.path.join(folder_path, filename), "wb") as file:
            response = requests.get(url)
            file.write(response.content)
    output_directory = "./images_down"
    # Open the text file and iterate over each line
    with open(text_file_path, "r") as file:
        for line in file:
            # Check if the line contains links ending in .gif or .jjpg
            if "gif" in line or "jpg" in line or "jpeg" in line:
            # if "Swatch Links:" in line:
                # Extract all URLs from the following lines until a line break or the end of the file
                for next_line in file:
                    if next_line.strip():  # Check if the line is not empty
                        swatch_urls = extract_urls(next_line)
                        print(swatch_urls)
                        for url in swatch_urls:
                            # Open link in browser
                            webbrowser.open(url)
                            # Save image from URL tofolder 
                            # save_image_from_url(url, "./images_down")  
                            save_image_from_url(url, output_directory)  
                    else:
                        break  # Exit the loop if a line break is encountered

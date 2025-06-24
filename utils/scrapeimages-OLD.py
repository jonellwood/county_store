from bs4 import BeautifulSoup
import requests
import re
import os 
import webbrowser
from urllib.parse import urlparse

def remove_special_characters(input_string):
    pattern = r"[^a-zA-Z0-9\s'/+]"
    cleaned_string = re.sub(pattern, ' ', input_string)
    return cleaned_string

def extract_href(tag):
    return tag['href'] if tag else None

def extract_urls(line):
    urls = []
    # Split the line by whitespace and iterate over each word
    for word in line.split():
        # Check if the word starts with "http://" or "https://"
        if word.startswith("http://") or word.startswith("https://"):
            urls.append(word)
    return urls

### trying to keep this in order of events occurring. Functions above are just helpers - dont worry about those little guys.
def extract_colors_from_file(file_path):
    colors = []
    with open(file_path, 'r') as file:
        lines = file.readlines()
        for line in lines:
            if line.startswith('+'):
                colors.append(line.strip('- ').strip())
    return colors


directory = './out_stage_two'
def process_text_files_in_directory(directory):
    print('🐍 + 🖥️ = 🚨')
    
    filenames = []  # Move the declaration outside the loop
    # Iterate over all files in the specified directory
    for filename in os.listdir(directory):
        # Check if the file is a text file
        if filename.endswith(".processed") and filename.startswith("SM-"):  # Ensure it's a file created by scrape_product_data
            # Construct the full path to the text file
            file_path = os.path.join(directory, filename)
            filenames.append(file_path)
            # Get the product number from the filename (assuming the format is 'product_number.txt')
            product_number = os.path.splitext(filename)[0]
            # Construct the directory path with product number
            output_directory = os.path.join(directory, product_number)
            print(output_directory)
            # Call open_links_from_text_files for each text file with the new directory path
            # open_links_from_text_files(filenames)
            # Move or rename the text file
            # new_file_path = file_path + ".processed"  # Example: Add ".processed" to the file name
            # os.rename(file_path, new_file_path)
    return filenames

process_text_files_in_directory(directory)


def open_links_from_text_files(text_files):
    # Iterate over each text file
    for text_file in text_files:
        # Open the text file and iterate over each line
        with open(text_file, "r") as file:
            for line in file:
                # Extract all URLs from the line
                urls = extract_urls(line)
                for url in urls:
                    # Check if the URL ends with ".gif" or ".jpg"
                    if url.endswith(".gif") or url.endswith(".jpg"):
                        # Open link in browser
                        webbrowser.open(url)
                        # Save image from URL to folder 
                        save_image_from_url(url, "./images_down")
        # Move or rename the text file
        new_file_path = text_file + ".processed"  # Example: Add ".processed" to the file name
        os.rename(text_file, new_file_path)

def save_image_from_url(url, folder_path):
    # Extract filename from URL
    filename = os.path.basename(urlparse(url).path)
    # Save image to specified folder
    with open(os.path.join(folder_path, filename), "wb") as file:
        response = requests.get(url)
        file.write(response.content)


base_url = 'https://www.sanmar.com/p/6639_'
colors = ['Black', 'OxfordBlue', 'Navy', 'White'];
file_path = './out_stage_two/SM-L659.txt'
# colors = extract_colors_from_file(file_path)
url_append = '#?doScrollToGrid=true'


def scrape_product_images(url):
    page = requests.get(url)
    soup = BeautifulSoup(page.text, features='lxml')
    
    # Get product name
    name = soup.find('h1')
    product_name = remove_special_characters(name.text)
    # Get a list of all links ending in .jpg if possible.
    image_holder = soup.find('div', class_='thumbnails').find_all('a')
    src_values = [a['href'] for a in image_holder]
    if src_values:
        for src in src_values:
            new_src = 'https:' + src
            print('Here is the URL')
            print(new_src)

    # print(src_values)

for color in colors:
    constructed_url = base_url + color + url_append
    print(constructed_url)
    scrape_product_images(constructed_url)



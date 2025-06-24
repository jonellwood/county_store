from bs4 import BeautifulSoup
import requests
import re
import os 
import webbrowser
from urllib.parse import urlparse
from remove_pa_string import remove_pa_from_gif_files
from remove_swatch_string import remove_swatch_from_gif_files
from rename_port_colors import remove_prefix_from_gif_files
# from make_insert_sql import extract_colors_from_file
# from make_insert_sql import process_text_files

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

def save_image_from_url(url, folder_path):
        # Extract filename from URL
        filename = os.path.basename(urlparse(url).path)
        # Save image to specified folder
        with open(os.path.join(folder_path, filename), "wb") as file:
            response = requests.get(url)
            file.write(response.content)
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
    # output_directory = "./images_down"
    # Open the text file and iterate over each line
    # with open(text_file_path, "r") as file:
    #     for line in file:
    #         # Check if the line contains links ending in .gif or .jjpg
    #         if "gif" in line or "jpg" in line or "jpeg" in line:
    #         # if "Swatch Links:" in line:
    #             # Extract all URLs from the following lines until a line break or the end of the file
    #             for next_line in file:
    #                 if next_line.strip():  # Check if the line is not empty
    #                     swatch_urls = extract_urls(next_line)
    #                     print(swatch_urls)
    #                     for url in swatch_urls:
    #                         # Open link in browser
    #                         webbrowser.open(url)
    #                         # Save image from URL tofolder 
    #                         # save_image_from_url(url, "./images_down")  
    #                         save_image_from_url(url, output_directory)  
    #                 else:
    #                     break  # Exit the loop if a line break is encountered

def process_text_files_in_directory(directory):
    print('Charming the 🐍')
    print('Reading the file hisssssstory');
    filenames = []
    # Iterate over all files in the specified directory
    for filename in os.listdir(directory):
        # Check if the file is a text file
        if filename.endswith(".txt"):
            # Construct the full path to the text file
            file_path = os.path.join(directory, filename)
            filenames.append(file_path)
            # Get the product number from the filename (assuming the format is 'product_number.txt')
            product_number = os.path.splitext(filename)[0]
            # Construct the directory path with product number
            output_directory = os.path.join(directory, product_number)
            print(output_directory)
            # Call open_links_from_text_file for each text file with the new directory path
            open_links_from_text_files(filenames)
    return filenames

# directory_path = './out'
# process_text_files_in_directory(directory_path)
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

# def open_links_from_text_file(text_file_path):
#     # Function to extract URLs from lines containing links
    # def extract_urls(line):
    #     urls = []
    #     # Split the line by whitespace and iterate over each word
    #     for word in line.split():
    #         # Check if the word starts with "http://" or "https://"
    #         if word.startswith("http://") or word.startswith("https://"):
    #             urls.append(word)
    #     return urls

    # Function to save image from URL
    def save_image_from_url(url, folder_path):
        # Extract filename from URL
        filename = os.path.basename(urlparse(url).path)
        # Save image to specified folder
        with open(os.path.join(folder_path, filename), "wb") as file:
            response = requests.get(url)
            file.write(response.content)
    
    # output_directory = "./images_down"
    # Open the text file and iterate over each line
    # with open(text_file_path, "r") as file:
    #     for line in file:
    #         # Check if the line contains links ending in .gif or .jjpg
    #         if "gif" in line or "jpg" in line or "jpeg" in line:
    #         # if "Swatch Links:" in line:
    #             # Extract all URLs from the following lines until a line break or the end of the file
    #             for next_line in file:
    #                 if next_line.strip():  # Check if the line is not empty
    #                     swatch_urls = extract_urls(next_line)
    #                     print(swatch_urls)
    #                     for url in swatch_urls:
    #                         # Open link in browser
    #                         webbrowser.open(url)
    #                         # Save image from URL tofolder 
    #                         # save_image_from_url(url, "./images_down")  
    #                         save_image_from_url(url, output_directory)  
    #                 else:
    #                     break  # Exit the loop if a line break is encountered
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

def scrape_product_data(url):
    page = requests.get(url)
    soup = BeautifulSoup(page.text, features='lxml')

    # Extract product name
    name = soup.find('h1')
    product_name = remove_special_characters(name.text)

    # Extract product number text
    name_holder = soup.find('div', class_='color-swatches')
    first_a_tag = soup.find('div', class_='color-swatches').find('a')
    product_number = 'SM-' + first_a_tag['data-style-number']
    # product_number = soup.find('span', class_='product-style-number').text

    # Extract text for sizes
    sizes = soup.find('div', class_='sizes').text.strip()

    # Extract each color name
    colors = soup.find_all('span', class_='swatch-name')
    color_names = [color.text for color in colors]

    # Extract spec sheet href
    spec_sheet_a = soup.find('li', class_='product-specsheet-li').find('a')
    spec_sheet_href = extract_href(spec_sheet_a)

    # Extract main image href
    main_img_a = soup.find('div', class_='main-image').find('a')
    main_img_href = extract_href(main_img_a)

    # Extract link for each color swatch
    color_info_div = soup.find('div', class_='swatches')
    swatch_links = []
    if color_info_div:
        color_list = color_info_div.find_all('ul')
        for ul in color_list:
            for li in ul.find_all('li'):
                img_tag = li.find('img')
                if img_tag:
                    src_value = 'https:' + img_tag['src']
                    swatch_links.append(src_value)

     # Create directory if it doesn't exist
    # output_directory = f"./out/{product_number}"
    output_directory = f"./out"
    os.makedirs(output_directory, exist_ok=True)

    # Write data to a text file
    # output_filename = f"./out/{product_number}/{product_number}.txt"
    output_filename = f"{output_directory}/{product_number}.txt"
    product_directory = f"{output_directory}"
    with open(output_filename, 'w') as file:
        file.write(f"Product Name: {product_name}\n")
        file.write(f"Product Number: {product_number}\n")
        file.write(f"Sizes: {sizes}\n")
        file.write("Colors:\n")
        for color in color_names:
            file.write(f"+ {color}\n")
        if spec_sheet_href:
            file.write(f"Spec Sheet: {spec_sheet_href}&pdf=Y\n")
        if main_img_href:
            file.write(f"Main Image: https:{main_img_href}\n")
        file.write("Swatch Links:\n")
        for link in swatch_links:
            file.write(f"- {link}\n")
    process_text_files_in_directory(product_directory)
    print(f"Data written to {output_filename}")
    
    
    


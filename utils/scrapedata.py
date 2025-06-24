from bs4 import BeautifulSoup
import requests
import re
import os 
import webbrowser
from urllib.parse import urlparse

def clip_to_255(inp_str):
    clipped = inp_str[:255]

    if len(clipped) == 255 and not clipped.endswith(' '):
        last_space_index = clipped.rfind(' ')
        clipped = clipped[:last_space_index]

    return clipped

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

def process_text_files_in_directory(directory):
    print('Charming the 🐍')
    print('Reading the file hisssssstory')
    filenames = []  # Move the declaration outside the loop
    # Iterate over all files in the specified directory
    for filename in os.listdir(directory):
        # Check if the file is a text file
        if filename.endswith(".txt") and filename.startswith("SM-"):  # Ensure it's a file created by scrape_product_data
            # Construct the full path to the text file
            file_path = os.path.join(directory, filename)
            filenames.append(file_path)
            # Get the product number from the filename (assuming the format is 'product_number.txt')
            product_number = os.path.splitext(filename)[0]
            # Construct the directory path with product number
            output_directory = os.path.join(directory, product_number)
            print(output_directory)
            # Call open_links_from_text_files for each text file with the new directory path
            open_links_from_text_files(filenames)
            # Move or rename the text file
            # new_file_path = file_path + ".processed"  # Example: Add ".processed" to the file name
            # os.rename(file_path, new_file_path)
    return filenames

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
                        # webbrowser.open(url) ##I dont think I need to actually do this.
                        # Save image from URL to folder 
                        save_image_from_url(url, "./images_down")
                        print(f"downloading image from  {url}")
        # Move or rename the text file
        new_file_path = text_file + ".processed"  # Example: Add ".processed" to the file name
        os.rename(text_file, new_file_path)

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

    #Extract description
    # desc_tag = soup.find('div', class_ = 'description')
    # # print(desc_tag)
    # if desc_tag:
    #     desc = desc_tag.find('p').text.strip() 
    #     desc = clip_to_255(desc)
    # else:
    #     desc = 'Desc Not found'
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

    # Extract Link for image each color option
    color_options_div = soup.find('div', class_='swatches')
    img_links = [];
    if color_options_div:
        link_list = color_options_div.find_all('ul')
        for ul in link_list:
            for li in ul.find_all('li'):
                a_tag = li.find('a')
                if a_tag:
                    href_value = 'https://www.sanmar.com' + a_tag['href']
                    img_links.append(href_value)


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
    output_directory = "./out"
    os.makedirs(output_directory, exist_ok=True)

    # Write data to a text file
    output_filename = f"{output_directory}/{product_number}.txt"
    product_directory = f"{output_directory}"
    with open(output_filename, 'w') as file:
        file.write(f"Product Name: {product_name}\n")
        file.write(f"Product Number: {product_number}\n")
        # if desc_tag:
        #     file.write(f"Description: {desc} \n")
        # else:
        #     file.write(f"Description not found \n")
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
        file.write('Image Option Links:\n')
        for link in img_links:
            file.write(f"* {link}\n")
    process_text_files_in_directory(product_directory)
    print(f"Data written to {output_filename}")

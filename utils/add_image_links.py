from bs4 import BeautifulSoup
import requests
import re
import os 
import webbrowser
from urllib.parse import urlparse

prod_img_path = './temp_prod_img'
folder_path = './out'
file_path = './out'
url_append = '#?doScrollToGrid=true'

def remove_spaces(input_string):
    return input_string.lstrip('+ ').replace(" ", "")
def get_prod_num(str, prod_color):
    if prod_color in str:
        return str.split(prod_color)[0]
    else: 
        return str


def save_image_from_url(url, folder_path):
    # Extract filename from URL
    filename = os.path.basename(urlparse(url).path)
    # Save image to specified folder
    with open(os.path.join(folder_path, filename), "wb") as file:
        response = requests.get(url)
        file.write(response.content)
        

def scrape_image_link(url):
    page = requests.get(url)
    soup = BeautifulSoup(page.text, features='lxml')

    image_tag = soup.find("img", class_="product-image-logoizer")
    # print(image_tag['src'])
    image_src = 'https:' + image_tag['src']
    # print(image_src)
    print('Getting image from ' + url)
    save_image_from_url(image_src, prod_img_path)

def process_file(file_path):
    print(file_path)
    with open(file_path, 'r') as file:
        lines = file.readlines()
        colors = []
        image_urls = []
        new_image_urls = []

        for line in lines:
            if line.startswith('*'):
                image_urls.append(line.split('* ')[1].strip())
        for url in image_urls:
            # webbrowser.open(url) # Do I need this????
            scrape_image_link(url)
                    

        with open(file_path, 'a') as file:
            file.write('\nImageLinks:\n')
            for url in new_image_urls:
                file.write(url + '\n')

for file_name in os.listdir(folder_path):
    if file_name.endswith('.processed'):
        file_path = os.path.join(folder_path, file_name)
        process_file(file_path)

print("Now go run prod_image_rename.py")

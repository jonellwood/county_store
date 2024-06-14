from bs4 import BeautifulSoup
import requests
import re
import os 
import webbrowser
from urllib.parse import urlparse


def get_hex(str):
    url = 'https://htmlcolorcodes.com/color-names/'
    page = requests.get(url)
    soup = BeautifulSoup(page.text, features='lxml')
    parts = str.strip().split('/')
    # hex_values = []  # List to store found hex values
    color_dict = {}  # List to store found hex values
    for part in str.strip().split('/'):
        # Search for matching color name in 'color-table__cell--name' elements
        for name_element in soup.find_all('td', class_='color-table__cell--name'):
            if name_element.text.strip().replace(' ', '') == part.strip().replace(' ', ''):  # Case-insensitive match
                # Find the next sibling element (assuming hex value is next)
                next_sibling = name_element.find_next_sibling('td')
                if next_sibling and next_sibling.has_attr('class') and 'color-table__cell--hex' in next_sibling['class']:
                    # Extract hex value from sibling element
                    hex_value = next_sibling.text.strip()
                    # hex_values.append(hex_value)
                    color_dict[part.strip()] = hex_value   # Append hex value to the list

    # return hex_values  # Return the list of hex values
    return color_dict  # Return the list of hex values

# print (get_hex('Patriot Blue / Flame Red / White'))
# print (get_hex('Patriot Blue / White'))
# print (get_hex('White / NavajoWhite'))
print(get_hex('Columbia Blue / White / Navy'))



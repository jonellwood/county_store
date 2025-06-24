import os
import sys
import datetime

from remove_swatch_string import remove_swatch_from_gif_files
from rename_port_colors import remove_prefix_from_gif_files
from remove_pa_string import remove_pa_from_gif_files
from remove_haynes_string import remove_haynes_from_gif_files
from remove_mm_string import remove_mm_from_gif_files
from remove_ogio_string import remove_ogio_from_gif_files
from remove_cornerstone_string import remove_cornerstone_from_gif_files
from remove_sporttek_string import remove_sporttek_from_gif_files
from remove_portco_string import remove_portco_from_gif_files
from remove_portauthority_string import remove_portauthority_from_gif_files
from remove_st_string import remove_st_from_gif_files
from remove_pcx_string import remove_pc_prefix_from_gif_files
from remove_bgx_string import remove_bg_prefix_from_gif_files
from lowercase_all import lowercase_all
from remove_product_numbers import remove_product_names
from remove_after_hyphen import remove_after_hyphen
from copy_color_files import compare_folders
from remove_main_prod_image import remove_main_prod_image 

## we are using remove main product image to delete this since we do not need them 
## right now, but may in the future.If so we just comment this function call out
# remove_main_prod_image('/images_down')

def prune_colors(folder_path):
    timestamp = datetime.datetime.now().strftime("%Y-%m-%d_%H-%M-%S")
    log_file_name = f"output_log_{timestamp}.txt"
    with open(log_file_name, 'w') as log_file:

        folder_path = './images_down'
        file_path = './out'

        sys.stdout = log_file

        print(f"Making all names lowercase")
        lowercase_all(folder_path)

        print(f"Attempting to remove all product numbers from color names")
        remove_product_names(file_path, folder_path)

        print(f"Removing ~swatch~ from color names")
        remove_swatch_from_gif_files(folder_path)

        print(f"Removing ~port~ from color names")
        remove_prefix_from_gif_files(folder_path)

        print(f"Removing ~PA- ~ from color names")
        remove_pa_from_gif_files(folder_path)

        print(f"Removing ~Hanes- ~ from color names")
        remove_haynes_from_gif_files(folder_path)

        print(f"Removing ~MM & MercerMettle- ~ from color names")
        remove_mm_from_gif_files(folder_path)

        print(f"Removing ~Ogio- ~ from color names")
        remove_ogio_from_gif_files(folder_path)

        print(f"Removing ~Cornerstone- ~ from color names")
        remove_cornerstone_from_gif_files(folder_path)

        print(f"Removing ~SportTek- ~ from color names")
        remove_sporttek_from_gif_files(folder_path)

        print(f"Removing ~PortCo- ~ from color names")
        remove_portco_from_gif_files(folder_path)

        print(f"Removing ~PortAuthority- ~ from color names")
        remove_portauthority_from_gif_files(folder_path)

        print(f"Removing ~ST- ~ from color names")
        remove_st_from_gif_files(folder_path)

        print(f"Removing ~Anything starting with PC*- ~ from color names")
        remove_pc_prefix_from_gif_files(folder_path)

        print(f"Removing ~Anything starting with BG*- ~ from color names")
        remove_bg_prefix_from_gif_files(folder_path)

        print(f"Cleaning up anything with a hyphen")
        remove_after_hyphen(folder_path)

        sys.stdout = sys.__stdout__

prune_colors('./images_down')
# colors_directory = './out'
source_folder = './images_down'
final_destination_folder = './images_moved'
compare_folders(source_folder, final_destination_folder)
print(f"Now run add_image_links.py")
# process_text_files(colors_directory)


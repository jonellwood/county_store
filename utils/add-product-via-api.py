import os
import requests
import re
import traceback

directory = './out'
final_directory = './products_added'
def remove_hash(input_string):
    return input_string.replace('# ', '')

# This is a stupid function to have to have to correct my bad decisions in setting up the sizes table
# when we first started. It takes the color_index value generated in a previous function and 
# converts it to the corresponding color_id in the database. It's dumb, but so am I. So here we are.
def change_stmt(val):
    switch_dict = {
        # index, id
        0: 10,
        1: 1,
        2: 2,
        3: 3,
        4: 4,
        5: 5,
        6: 6,
        7: 7,
        8: 8,
        9: 30,
        10: 19,
        11: 19,
        12: 20,
        13: 21,
        14: 12,
        15: 13,
        16: 14,
        17: 15,
        18: 16,
        30: 9,
        
    }
    return switch_dict.get(val, None)

def change_index_to_id(sizes_str):
    sizes_list = sizes_str.split(',')
    results = [change_stmt(int(size)) for size in sizes_list]
    return results

for filename in os.listdir(directory):
    if filename.endswith(".processed"):
        # print(filename);
        with open(os.path.join(directory, filename), 'r') as file:
            lines = file.readlines()
            # print(lines[2])
            code = lines[1].split(': ')[1].strip()
            name = lines[0].split(': ')[1].strip()
            desc = lines[2].split(': ')[1].strip()
            
            colors = []
            for line in lines:
                if line.startswith('# '):
                    color_id = line.split('-')[1].strip()
                    colors.append(color_id)

            
            colors_str = ','.join(colors)

            sizes = []
            for line in lines:
                if line.startswith('['):
                    size_index = line.split(',')
                    sizes.append(size_index)

            cleaned_sizes = [item.strip("[").strip("]").strip().replace(" ", "") for sublist in sizes for item in sublist]
            sizes_str = ','.join(cleaned_sizes)
            fixed_sizes = change_index_to_id(sizes_str.strip("[").strip("]").strip().replace(" ", ""))
            # print(fixed_sizes);
            fixed_str = ', '.join(str(x) for x in fixed_sizes)
            # print('sizes: ', sizes_str)
            # print('fixed: ', fixed_str)
            # print(fixed_str)

            payload = {
                'code': code,
                'name': name,
                'desc': desc,
                'colors': colors_str,
                'sizes': fixed_str
            }
            # print(payload)
         
            try:
                response = requests.get('https://store.berkeleycountysc.gov/utils/add-product-api.php', params=payload)
               
                new_filename = filename + ".done"
                print(f"{final_directory} / {new_filename}")
                os.rename(os.path.join(directory, filename), os.path.join(final_directory, new_filename))
                data = response.json()  # Decode JSON response
                print(data['insert_id'])
                print(data['colors'])
                # Add .done to the file name so we know it been done, but will also stop if from being added to the database - and moving to './products_added folder
                # new_filename = os.path.splitext(filename)[0] + ".done"
            except requests.exceptions.RequestException as e:
                print(f"Failed to send product information for {code} - {name}. Error: {e}")
                print(f"Full exception traceback: {traceback.format_exc()}")

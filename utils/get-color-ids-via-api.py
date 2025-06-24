import os
import requests
import json

directory = './out'

def replace_slash(input_string):
    return input_string.replace('/', ' /')

for filename in os.listdir(directory):
    if filename.endswith(".processed"):
        with open(os.path.join(directory, filename), 'r') as file:
            
            lines = file.readlines()
            colors = []
            for line in lines:
                if line.startswith('+'):
                    colors.append(line.split("+ ")[1].strip())
                    # print(colors)
            ###########TRYING MOVING THIS 
            with open(os.path.join(directory, filename), 'a') as outfile:
                outfile.write('Color Name - ID Pairs:\n')
                
            for color in colors:
                # print(replace_slash(color))
                payload = {'color': replace_slash(color)}
                # print('$$$$$$$$$$PAYLOAD$$$$$$$$$$$')
                # print(payload)
                try:
                    response = requests.get('https://store.berkeleycountysc.gov/utils/get-color-id-api.php', params=payload)
                    if response.status_code == 200:
                        print(response.json())  # Print API response
                        colors = response.json() 
                        with open(os.path.join(directory, filename), 'a') as outfile:
                            for color in colors:
                                outfile.write(f"# {color['color']} - {color['color_id']}\n")
                            # outfile.write('Color Name - ID Pairs:\n')
                            # json.dump(response.json(), outfile)  # Write response to a file
                except requests.exceptions.RequestException as e:
                    print(f"Failed to get color code for {color} Error: {e}")
            print("Now run extract_size_value.py")
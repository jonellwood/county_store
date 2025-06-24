import os

start_img_path = './images_down'
final_img_path = './temp_prod_img'

def rename_main_prod_image(start_img_path):
    for filename in os.listdir(start_img_path):
        if filename.endswith(".jpg"):
            try: 
                parts = filename.split('-')
                prod_color = parts[1]
                # print(parts)
                # print('------------------')
                # print(prod_color)
                prod_name = parts[3]
                # print(prod_name)
                split_parts = prod_name.split(prod_color)
                result = split_parts[0]
                # print(result)
                new_filename = 'SM-' + result + '.jpg'
                os.rename(os.path.join(start_img_path, filename), os.path.join(final_img_path, new_filename))
                print(f"Renaming {start_img_path}'/'{filename} to {final_img_path}'/'{new_filename}")
            except Exception as e:
                print(f"Error processing {filename} : {e}")
    print("Now run prune_colors.py")
rename_main_prod_image(start_img_path)
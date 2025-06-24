import os 

# prod_img_path = '../product-images'
prod_img_path = './temp_prod_img'

color_pairs = [
('hddstysage','heathereddustysage'),
('hthrdcharcoal','heatheredcharcoal'),
('htddstypch','heathereddustypeach'),
('htdlvndr','heatheredlavender'),
('htdteal','heatheredteal'),
('marfrost','maritimefrost'),
('marifrost','maritimefrost'),
('milgrnfst','militarygreenfrost'),
('nvyfrost','navyfrost'),
('prplfrost','purplefrost'),
('turqfrost', 'turquoisefrost'),
('wstriahthr', 'wisteriaheather'),
('ltbluenavy', 'lightbluenavy'),
('petrolblnavy', 'petrolbluenavy'),
('sftyorg', 'safetyorange'),
('sftyyellow', 'safetyyellow'),
('hthrddeeproy', 'heathereddeeproyal'),
('hthrdred', 'heatheredred'),
('battleshipgry', 'battleshipgrey'),
('blackcharhth', 'blackcharcoalheather'),
('pearlgreyhth', 'pearlgreyheather'),
('riverblnv', 'riverbluenavy'),
('nsbldsbnv', 'nightskybluedressnavy'),
('carolinabl', 'carolinablue'),
('brtkelgn', 'brightkellygreen'),
('blkbtlgy', 'blackbattleshipgrey'),
('dpforgrn', 'deepforestgreen'),
('dsbnvbtgy', 'dressbluenavybattleshipgrey'),
('txorange', 'texasorange'),
('sunflwrylw', 'sunfloweryellow'),
('trplpink', 'tropicalpink'),
('mediblue', 'mediterraneanblue'),
('ultramrnbl', 'ultramarineblue'),
('charhtgrey', 'charcoalheathergrey'),
('brtlvndr', 'brightlavender'),
('clovergrn', 'clovergreen'),
('pinkrspbry', 'pinkraspberry'),
('royalblk', 'royalblack'),
('engnrdbk', 'engineredblack'),
('blkirgy', 'blackirongrey'),
('brillblue', 'brilliantblue'),
('brtpurple', 'brightpurple'),
('nvbllake', 'navybluelake'),
('charcoalht', 'charcoalheather'),
('oxfordhthr', 'oxfordheather'),
('sterlinggy', 'sterlinggrey'),
('aquaticbl', 'aquaticblue'),
('athletichthr', 'athleticheather'),
('athleticmarn', 'athleticmaroon'),
('greyhthr', 'greyheather'),
('athlhthr', 'athleticheather'),
('marshmllw', 'marshmellow'),
('navyhthr', 'navyheather'),
('oaththr', 'oatmealheather'),
('poprasphtr', 'popraspberryheather'),
('sftygrn', 'safetygreen'),
('slickerylw', 'slickeryellow'),
('dknvhthr', 'darknavyheather'),
('dkgyhthr', 'darkgreyheather'),
('dkcharhth', 'darkcharcoalheather'),
('amthstprpl', 'amethystpurple'),
('dsblnavy', 'dressbluenavy'),
('sunfloweryllw', 'sunfloweryellow'),
('trplpink', 'tropicalpink'),
('txorange', 'texasorange'),
('ultramrnbl', 'ultramarineblue'),
('ultramarnblue', 'ultramarineblue'),
('violetprpl', 'violetpurple'),
('whtltstn', 'whitelightstone'),
('royclsnvy', 'royalclassicnavy'),
('redltstn', 'redlightstone'),
('nvltstone', 'navylightstone'),
('blkltstn', 'blacklightstone'),
('brtlavender', 'brightlavender'),
('burgltst', 'burgundylightstone'),
('dkgreennv', 'darkgreennavy'),
('charhthrgrey', 'charcoalheathergrey'),
('lblulstn', 'lightbluelightstone'),
('lstnclsnv', 'lightstoneclassicnavy'),
('medblue', 'mediterraneanblue'),
('autumnorng', 'autumnorange'),
('dpblackgraph', 'deepblackgraphite'),
('richreddpbk', 'richreddeepblack'),
('rvblnvwht', 'riverbluenavywhite'),
('blkthgrey', 'blackthundergrey'),
('grphwhite', 'graphitewhite'),
('rgtblgsgy', 'regattabluegustygrey'),
('vioprpblk', 'violetpurpleblack'),
('voltagebl', 'voltageblue'),
('blwakehtht', 'bluewakeheather'),
('cobaltht', 'cobaltheather'),
('dkhtgry', 'darkheathergrey'),
('dksmkgy', 'darksmokegrey'),
('dporhthr', 'deeporangeheather'),
('forestgnht', 'forestgreenheather'),
('forestgrn', 'forestgreen'),
('gphheather', 'graphiteheather'),
('gyconcrete', 'greyconcrete'),
('mdhtgrey', 'mediumheathergrey'),
('pkrasht', 'pinkraspberryheather'),
('scarhthr', 'scarletheather'),
('teamcard', 'teamcardinal'),
('trnvyhthr', 'truenavyheather'),
('turfgrnhtr', 'turfgreenheather'),
('varprplht', 'varsitypurpleheather'),
('vtghtr', 'vintageheather')
]

def rename_color(prod_img_path):
    for filename in os.listdir(prod_img_path):
        if filename.endswith(".jpg"):
            try:
                parts = filename.split('_')
                prod_color = parts[0]
                for pair in color_pairs:
                    if prod_color == pair[0]:
                        basename = parts[1]
                        new_filename = pair[1] + '_' + basename
                        os.rename(os.path.join(prod_img_path, filename), os.path.join(prod_img_path, new_filename))
                        print(f"Renaming {filename} to {new_filename}")
                        break
            except Exception as e:
                print(f"Error processing {filename}: {e}")

    print("Now run add_sm.py")
rename_color(prod_img_path)


# def rename_color(prod_img_path):
#     for filename in os.listdir(prod_img_path):
#         if filename.endswith(".jpg"):
#             try:
#                 parts = filename.split('_')
#                 prod_color = parts[0]
#                 if prod_color == old_colorname:
#                     # print(prod_color)
#                     basename = parts[1]
#                     # print(basename)
#                     new_filename = new_colorname + '_' + basename
#                     # print(new_filename)
#                     os.rename(os.path.join(prod_img_path, filename), os.path.join(prod_img_path, new_filename))
#                     print(f"Renaming {filename} to {new_filename}")

#             except Exception as e:
#                 print(f"Error processing {filename}: {e}")

# rename_color(prod_img_path)
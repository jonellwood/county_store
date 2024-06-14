def clip_to_255(inp_str):
    clipped = inp_str[:255]

    if len(clipped) == 255 and not clipped.endswith(' '):
        last_space_index = clipped.rfind(' ')
        clipped = clipped[:last_space_index]
    print(clipped)
    return clipped

clip_to_255("We took our legendary Silk Touch Polo and made it work even harder. The durable, easy care Silk Touch Performance Polo wicks moisture, resists snags and thanks to PosiCharge® technology, holds onto its color for a professional look that lasts. There's just no higher performing polo at this price.")
import sys
import os
import datetime
from PIL import Image, ImageDraw, ImageFont

def generate_receipt(nominal_rp, no_invoice, metode, nama_pemesan, nama_rombongan, nama_paket, jumlah_pax, output_path):
    width = 620
    height = 920

    # Colors
    bg_color = (248, 250, 252)
    card_color = (255, 255, 255)
    header_color = (15, 23, 42)
    primary_color = (30, 41, 59)
    accent_color = (255, 87, 34)
    green_success = (16, 185, 129)
    dark_green = (5, 150, 105)
    text_muted = (100, 116, 139)
    border_color = (226, 232, 240)
    subtle_bg = (241, 245, 249)
    white = (255, 255, 255)

    img = Image.new('RGB', (width, height), color=bg_color)
    draw = ImageDraw.Draw(img)

    # Fonts
    font_path_bold = "C:/Windows/Fonts/arialbd.ttf"
    font_path_reg = "C:/Windows/Fonts/arial.ttf"

    try:
        font_logo = ImageFont.truetype(font_path_bold, 24)
        font_head = ImageFont.truetype(font_path_bold, 17)
        font_sub = ImageFont.truetype(font_path_reg, 12)
        font_success = ImageFont.truetype(font_path_bold, 18)
        font_nominal = ImageFont.truetype(font_path_bold, 36)
        font_label = ImageFont.truetype(font_path_reg, 13)
        font_val = ImageFont.truetype(font_path_bold, 13)
        font_total_lbl = ImageFont.truetype(font_path_bold, 15)
        font_total_val = ImageFont.truetype(font_path_bold, 17)
    except:
        font_logo = font_head = font_sub = font_success = font_nominal = font_label = font_val = font_total_lbl = font_total_val = ImageFont.load_default()

    # Center card
    draw.rounded_rectangle([25, 25, width - 25, height - 25], radius=20, fill=card_color, outline=border_color, width=2)

    # Top dark header
    draw.rounded_rectangle([25, 25, width - 25, 115], radius=20, fill=header_color)
    draw.rectangle([25, 95, width - 25, 115], fill=header_color)

    # PUMA Logo Badge
    draw.rounded_rectangle([45, 45, 135, 95], radius=10, fill=accent_color)
    draw.text((62, 58), "PUMA", font=font_logo, fill=white)

    draw.text((150, 52), "PT. PUMA STAR JAYA TOUR", font=font_head, fill=white)
    draw.text((150, 78), "STRUK BUKTI PEMBAYARAN ELEKTRONIK RESMI", font=font_sub, fill=border_color)

    # Green checkmark circle
    center_x = width // 2
    circle_y = 168
    radius = 34
    draw.ellipse([center_x - radius, circle_y - radius, center_x + radius, circle_y + radius], fill=green_success)
    draw.line([(center_x - 14, circle_y), (center_x - 4, circle_y + 12), (center_x + 16, circle_y - 10)], fill=white, width=4)

    # Status text
    text_stat = "TRANSAKSI BERHASIL"
    bbox_stat = draw.textbbox((0, 0), text_stat, font=font_success)
    w_stat = bbox_stat[2] - bbox_stat[0]
    draw.text(((width - w_stat) // 2, 218), text_stat, font=font_success, fill=dark_green)

    text_sub = f"Pembayaran {metode} Telah Diterima Sistem"
    bbox_sub = draw.textbbox((0, 0), text_sub, font=font_sub)
    w_sub = bbox_sub[2] - bbox_sub[0]
    draw.text(((width - w_sub) // 2, 245), text_sub, font=font_sub, fill=text_muted)

    # Big Nominal
    bbox_nom = draw.textbbox((0, 0), nominal_rp, font=font_nominal)
    w_nom = bbox_nom[2] - bbox_nom[0]
    draw.text(((width - w_nom) // 2, 280), nominal_rp, font=font_nominal, fill=header_color)

    # Divider line
    draw.line([(50, 340), (width - 50, 340)], fill=border_color, width=2)

    # Key-value rows
    now_str = datetime.datetime.now().strftime("%d %B %Y, %H:%M:%S WIB")
    rrn = f"TRX{datetime.datetime.now().strftime('%Y%m%d')}{no_invoice[-4:]}"

    rows = [
        ("Waktu Transaksi", now_str),
        ("No. Referensi (RRN)", rrn),
        ("No. Invoice Resmi", no_invoice),
        ("Merchant Penerima", "PT. Puma Star Jaya Tour"),
        ("NMID / Merchant ID", "ID1025448727392 (zaa_twisty)"),
        ("Nama Pelanggan", nama_pemesan),
        ("Nama Rombongan", nama_rombongan),
        ("Program Paket", nama_paket),
        ("Jumlah Peserta", f"{jumlah_pax} Orang (Pax)"),
        ("Metode Pembayaran", metode.upper()),
        ("Biaya Layanan Admin", "Rp 0 (GRATIS)"),
        ("TOTAL DIBAYAR", nominal_rp)
    ]

    start_y = 360
    row_height = 34

    for i, (lbl, val) in enumerate(rows):
        cur_y = start_y + (i * row_height)
        is_total = (i == len(rows) - 1)

        if is_total:
            draw.rounded_rectangle([50, cur_y - 8, width - 50, cur_y + 32], radius=8, fill=subtle_bg, outline=border_color, width=1)
            draw.text((65, cur_y), lbl, font=font_total_lbl, fill=header_color)
            bbox_v = draw.textbbox((0, 0), val, font=font_total_val)
            w_v = bbox_v[2] - bbox_v[0]
            draw.text((width - 65 - w_v, cur_y - 2), val, font=font_total_val, fill=dark_green)
        else:
            draw.line([(50, cur_y + 24), (width - 50, cur_y + 24)], fill=border_color, width=1)
            draw.text((65, cur_y), lbl, font=font_label, fill=text_muted)
            bbox_v = draw.textbbox((0, 0), val, font=font_val)
            w_v = bbox_v[2] - bbox_v[0]
            draw.text((width - 65 - w_v, cur_y), val, font=font_val, fill=primary_color)

    # Footer Verified Badge
    foot_y = 790
    draw.rounded_rectangle([50, foot_y, width - 50, foot_y + 65], radius=10, fill=subtle_bg, outline=border_color, width=1)
    draw.text((70, foot_y + 14), "STATUS: PEMBAYARAN SAH & TERVERIFIKASI SISTEM", font=font_val, fill=dark_green)
    draw.text((70, foot_y + 36), "Struk elektronik ini diterbitkan secara otomatis oleh payment gateway PT. Puma Star Jaya Tour.", font=font_sub, fill=text_muted)

    # Save
    os.makedirs(os.path.dirname(output_path), exist_ok=True)
    img.save(output_path, "PNG", quality=95)
    print("SUCCESS")

if __name__ == "__main__":
    if len(sys.argv) < 9:
        print("Usage: generate_receipt.py <nominal_rp> <no_invoice> <metode> <nama_pemesan> <nama_rombongan> <nama_paket> <jumlah_pax> <output_path>")
        sys.exit(1)

    generate_receipt(
        sys.argv[1],
        sys.argv[2],
        sys.argv[3],
        sys.argv[4],
        sys.argv[5],
        sys.argv[6],
        sys.argv[7],
        sys.argv[8]
    )


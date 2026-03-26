from flask import Flask, render_template, request
import yt_dlp
import os

app = Flask(__name__)

PASTA_DOWNLOAD = r"D:\Pagode"

if not os.path.exists(PASTA_DOWNLOAD):
    os.makedirs(PASTA_DOWNLOAD)

@app.route("/", methods=["GET", "POST"])
def index():
    if request.method == "POST":
        url = request.form["url"]

        ydl_opts = {
            'format': 'bestaudio/best',
            'outtmpl': f'{PASTA_DOWNLOAD}/%(title)s.%(ext)s',
            'ignoreerrors': True,
            'ffmpeg_location': r'C:\Users\\Downloads\ffmpeg-8.1-essentials_build\ffmpeg-8.1-essentials_build\bin',
            'postprocessors': [{
                'key': 'FFmpegExtractAudio',
                'preferredcodec': 'mp3',
                'preferredquality': '192',
            }],
        }

        with yt_dlp.YoutubeDL(ydl_opts) as ydl:
            ydl.download([url])

        return "Download concluído!"

    return render_template("index.html")

app.run(debug=True)
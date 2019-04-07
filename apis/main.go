package main

import (
	"log"
	"os"
	"strconv"

	"github.com/disintegration/imaging"
)

func main() {
	src, err := imaging.Open(os.Args[1], imaging.AutoOrientation(true))
	if err != nil {
		log.Fatalf("failed to open image: %v", err)
	}

	// Resize the cropped image to width = 200px preserving the aspect ratio.
	h, _ := strconv.Atoi(os.Args[3])
	src = imaging.Resize(src, 0, h, imaging.Lanczos)

	// Create a new image and paste the four produced images into it.
	//	dst := imaging.New(400, 400, color.NRGBA{0, 0, 0, 0})

	// Save the resulting image as JPEG.
	err = imaging.Save(src, os.Args[2])
	if err != nil {
		log.Fatalf("failed to save image: %v", err)
	}
}

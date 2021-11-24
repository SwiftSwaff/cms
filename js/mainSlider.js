var mainSlider = document.getElementById("main-slider")
var interval = 0
function mainAutoplay(run) {
    clearInterval(interval)
    interval = setInterval(() => {
        if (run && slider) {
            slider.next()
        }
    }, 2000)
}

var slider = new KeenSlider(mainSlider, {
    slidesPerView: 3,
    centered: true,
    spacing: 0,
    loop: true,
    duration: 2000,
    dragStart: () => {
        mainAutoplay(false)
    },
    dragEnd: () => {
        mainAutoplay(true)
    }
})

mainSlider.addEventListener("mouseover", () => {
    mainAutoplay(false)
})
mainSlider.addEventListener("mouseout", () => {
    mainAutoplay(true)
})
mainAutoplay(true)
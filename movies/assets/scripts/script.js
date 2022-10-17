
// resets the requests
window.history.pushState('', 'New Page Title', './');

// intersection observer. This will add class "viewed" when the class gets into view.
const observer = new IntersectionObserver(e => {
    // console.log(e);
    e.forEach(entry => {
        if (entry.isIntersecting){
            entry.target.classList.add("viewed");
            setTimeout(()=>{
                entry.target.classList.remove("viewed");
            }, 600);
        }
    });
});

function setSeriesDetail(series_id){
    var div = document.getElementById("series"+series_id);
    console.log("series"+series_id, div);
    div.addEventListener("click", ev => {
        seriesDetail(series_id);
    });
}

function seriesDetail(series_id){
    document.getElementById("mode").value = "SERIES";
    var snum = document.createElement("input");
    snum.name = "SERIES_NUMBER";
    snum.value = series_id;
    var form = document.getElementById("form");
    form.appendChild(snum);
    form.submit();
}

window.onload = () => {
    // set observers to the class observable
    let elems = document.querySelectorAll(".observable");
    elems.forEach(e => {
        observer.observe(e);
    });

    let btns = document.querySelectorAll(".button");
    btns.forEach(e => {
        e.addEventListener("click", ev => {
            // console.log(e, ev);
            document.getElementById("mode").value = String(e.id).toUpperCase();
            document.getElementById("form").submit();
        });
    });

}
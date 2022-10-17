var remaining_seconds = 1;
document.getElementById("return_sec").innerHTML = remaining_seconds;

setInterval(()=>{
    remaining_seconds -= 1;
    if(remaining_seconds < 0) document.getElementById("form").submit();
    else document.getElementById("return_sec").innerHTML = remaining_seconds;
}, 1000);

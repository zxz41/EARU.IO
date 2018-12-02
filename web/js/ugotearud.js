let lastclick = new Date();
let logo = document.getElementsByClassName("face-img")[0];

function TranslateLogo(x, y) {
    logo.style.transform = "translate(-" + (50 + x) + "%, -" + (50 + y) + "%)";
}

function LogoStep(timestamp,amplify,start)
{
    let rot = Math.random() * Math.PI * 2;
    let random2 = Math.random();

    if (!start) start = timestamp;
    let progress = timestamp - start;
    progress = progress / 1000;
    let inv = 1 / (progress * 10);

    progress = progress * 50;

    let x = Math.sin(progress),
        y = Math.sin(progress) * random2;

    let x2 = x * Math.cos(rot) - y * Math.sin(rot);
    let y2 = x * Math.sin(rot) + y * Math.cos(rot);
    inv = inv > 1 ? 1 : inv;

    TranslateLogo(x2 * inv * amplify, y2 * inv * amplify);

    if (inv > 0.02)
        window.requestAnimationFrame(timestamp => LogoStep(timestamp,amplify,start));
}

let times = 0;
function AnimateLogo()
{
    if(times < 10)
    {
        times++;
        let now = new Date();
        let latency = (now - lastclick) / 1000;
        lastclick = now;

        let amplify = 1.0 - latency / 2.0;
        amplify = amplify < 0.0 ? 0.0 : amplify;
        amplify = amplify > 1.0 ? 1.0 : amplify;
        amplify = 1 + amplify * 15;

        window.requestAnimationFrame(timestamp => LogoStep(timestamp,amplify,null));
    }
    else
    {
        window.location.replace("https://earu.io/ugotearud.png");
    }
}
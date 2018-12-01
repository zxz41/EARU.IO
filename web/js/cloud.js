let elements = document.getElementsByClassName("file-row");
let clickeds = [];
let selected = "";

function OnRowClick(i,element)
{
    if(clickeds[i] != null)
    {
        let categories = element.getElementsByClassName("file-row-category");
        selected = categories[0].innerText;
    }
    else
    {
        let style = element.style;
        clickeds[i] = { Background: style.backgroundColor, Color: style.color };
        element.style.backgroundColor = "rgba(244, 47, 44, 0.65)";
        element.style.color = "#333333";

        let tonull = [];
        for(let index of clickeds.keys())
        {
            if(index == i) continue;
            let e = elements[index];
            let prestyle = clickeds[index];
            if(prestyle != null)
            {
                e.style.backgroundColor = prestyle.Background;
                e.style.color = prestyle.Color;
                tonull.push(index);
            }
        }

        for(let index of tonull)
            clickeds[index] = null;
    }
}

for(let i=0; i < elements.length; i++)
{
    let element = elements[i];
    element.addEventListener("click", () => OnRowClick(i,element));
}
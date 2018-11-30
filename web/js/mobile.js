// Page warning for mobile incompatibility
let mobilewarned = false;
function OnResize()
{
    let width = document.body.offsetWidth;
    if(width < 1100)
    {
        let filelist = document.getElementById("file-list-parent");
        if(width < 575)
        {
            filelist.style.marginRight = "-10%";
            let elements = document.getElementsByClassName("mobile-left-padding");
            for(let element of elements)
            {
                element.style.paddingLeft = "5%";
            }
        }
        else
        {
            filelist.style.marginRight = "0";
            let elements = document.getElementsByClassName("mobile-left-padding");
            for(let element of elements)
            {
                element.style.paddingLeft = "0";
            }
        }

        if(!mobilewarned)
        {
            let banner = document.getElementsByClassName("banner-left")[0];
            banner.style.display = "none";
            banner.classList.remove("col-sm-1");

            filelist.style.marginLeft = "0.5%";

            let maindiv = filelist.parentNode;
            maindiv.classList.remove("col-sm-11");
            maindiv.classList.add("col-sm-12");
            mobilewarned = true;
        }
    }
    else
    {
        if(mobilewarned)
        {
            let banner = document.getElementsByClassName("banner-left")[0];
            banner.style.display = "block";
            banner.classList.add("col-sm-1");

            let filelist = document.getElementById("file-list-parent")
            filelist.style.marginLeft = "-1.2%";

            let maindiv = filelist.parentNode;
            maindiv.classList.remove("col-sm-12");
            maindiv.classList.add("col-sm-11");
            mobilewarned = false;
        }
    }
}

window.addEventListener("resize",OnResize);
OnResize();
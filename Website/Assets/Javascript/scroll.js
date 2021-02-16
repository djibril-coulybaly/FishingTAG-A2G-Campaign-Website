window.onscroll = () => {
    const nav = document.querySelector('#navbar');
    if(this.scrollY <= 5) nav.className = ''; 
    else nav.className = 'scroll';
};

function scrollToAboutCampaign(){
    var element = document.getElementById('about-campaign');
    var headerOffset = 100;
    var elementPosition = element.offsetTop;
    var offsetPosition = elementPosition - headerOffset;

    window.scrollTo({
        top: offsetPosition,
        behavior: "smooth"
    });
}

function scrollToHowItWorks(){
    var element = document.getElementById('how-it-works');
    var headerOffset = 100;
    var elementPosition = element.offsetTop;
    var offsetPosition = elementPosition - headerOffset;

    window.scrollTo({
        top: offsetPosition,
        behavior: "smooth"
    });
}

function scrollToDonateNow(){
    var element = document.getElementById('donate-now');
    var headerOffset = 100;
    var elementPosition = element.offsetTop;
    var offsetPosition = elementPosition - headerOffset;

    window.scrollTo({
        top: offsetPosition,
        behavior: "smooth"
    });
}


function AboutCampaign() {
    window.location.href = "index.php#about-campaign";
}

function HowItWorks() {
    window.location.href = "index.php#how-it-works";
}

function DonateNow() {
    window.location.href = "index.php#donate-now";
}
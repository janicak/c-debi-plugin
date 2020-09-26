const $ = window.jQuery

const alterLayout = (postType) => {

  switch(postType){

    case "publication":
      // Move post_content to ACF message field, "Abstract"
      $('.acf-field-5eb9f05cc4075 .acf-input').append($('#postdivrich'))
      break;

    default:
      break;

  }

}


export default alterLayout
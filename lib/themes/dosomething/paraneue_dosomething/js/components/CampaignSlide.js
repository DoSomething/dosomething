import CampaignController from '../campaign/Controller';
import CampaignSignupCard from './CampaignSignupCard';

const React = require('react');

/**
 * CampaignSlide Component
 * <CampaignSlide />
 */
class CampaignSlide extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      campaigns: []
    };

    this.controller = new CampaignController();
  }

  componentDidMount() {
    const args = {
      'isStaffPick': true,
      'removeDuplicateResults': true,
      'sortByRelevance': true,
      'removeSignups': true
    };

    this.controller.getCampaigns(args).then(campaigns => {
      this.setState({
        campaigns: campaigns.splice(0, 3) // Grab the first 3 campaigns returned
      });
    });
  }

  render() {
    return (
      <div className={`slideshow__slide ${this.props.isActive ? '-active fade-in': ''}`}>
        <div className="container">
          <div className="wrapper">
            {this.renderHeader()}

            <div className="gallery -triad">
              {this.state.campaigns.map(campaign => {
                // Fix for local files / data missing by preventing null error
                const image_uri = campaign.cover_image.default != null ? campaign.cover_image.default.sizes.landscape.uri : '';
                return (
                  <li key={campaign.id}>
                    <CampaignSignupCard image_uri={image_uri} tagline={campaign.tagline}
                      title={campaign.title} signup={this.controller.signup} id={campaign.id} />
                  </li>
                );
              })}
            </div>
          </div>
        </div>
      </div>
    )
  }

  renderHeader() {
    return (
      <div className="container__block">
        <h2 className="heading -gamma">Woohoo! You're signed up.</h2>
        <p>As a DoSomething.org member, you're part of something bigger. You're part of a global movement for good.</p>

        <p>You'll get all the tools you need to create impact. You've already joined {this.props.campaign.title}. Now sign up for other popular campaigns!</p>
      </div>
    );
  }
}

CampaignSlide.analyticsIdentifier = 'Slide - Campaigns';

export default CampaignSlide;

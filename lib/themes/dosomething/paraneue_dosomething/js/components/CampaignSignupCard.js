import Button from './Button';
import { sendEvent } from '../utilities/Analytics';

const React = require('react');

/**
 * Campaign Signup Card Component
 * <CampaignSignupCard />
 */
class CampaignSignupCard extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      actionDisabled: false,
      isSignedUp: false,
      isLoading: false,
    };

    this.signup = this.preAction.bind(this);
    this.viewMore = this.preViewMore.bind(this);
  }

  preAction() {
    this.setState({
      actionDisabled: true,
      isLoading: true
    });

    this.props.signup(this.props.id, 'onboarding').then(() => {
      this.setState({
        isLoading: false,
        isSignedUp: true
      });

      sendEvent('onboarding-v2', 'signup', CampaignSignupCard.analyticsIdentifier);
    });
  }

  preViewMore() {
    sendEvent('onboarding-v2', 'view-more', CampaignSignupCard.analyticsIdentifier);
    this.props.viewMore();
  }

  renderHeader() {
    if (this.props.cardTitle) {
      return (
        <header className="beta-card__header">
          <div className="beta-card__row">
            <div className="beta-card__block">
              <h1 className="heading -beta -emphasized">{this.props.cardTitle}</h1>
            </div>
          </div>
        </header>
      );
    }

    return null;
  }

  renderForm() {
    let viewMore = null;
    if (this.props.viewMore) {
      viewAnother = (
        <li><Button type="tertiary" onClick={this.viewMore}>Show me another</Button></li>
      );
    }

    return (
      <ul className="form-actions">
        <li><Button reactive={true} loading={this.state.isLoading} disabled={this.state.actionDisabled} success={this.state.isSignedUp} onClick={this.signup}>Sign Up</Button></li>
        {viewMore}
      </ul>
    );
  }

  render() {
    const header = this.renderHeader();
    return (
      <article className={`beta-card ${header === null ? '-headerless' : ''}`}>
        <div className="beta-card__body">

          {header}

          <div className="beta-card__row">
            <img src={this.props.image_uri} />
          </div>

          <div className="beta-card__row">
            <div className="beta-card__block -centered">
              <h2 className="heading -delta">{this.props.title}</h2>
              <p className="tagline">{this.props.tagline}</p>
              {this.renderForm()}
            </div>
          </div>

        </div>
      </article>
    )
  }
}

CampaignSignupCard.analyticsIdentifier = 'Card - Campaign Signup';

export default CampaignSignupCard;

import React from "react";
import Test from "./Test";
import withAPI from "../components/withAPI";
import { withRouter } from "react-router";

class TestPage extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      questions: null,
    };
    this.submit = this.submit.bind(this);
    this.reset = this.reset.bind(this);
  }

  async componentDidMount() {
    const questions = await this.props.api.test(this.props.match.params.id);
    this.setState({ questions });
  }

  componentDidUpdate(prevProps, prevState) {
    if (!prevState.results && this.state.results) {
      document.body.scrollIntoView({ behavior: "smooth", block: "start" });
    }
  }

  async submit(entries) {
    const id = this.props.match.params.id;
    const results = await this.props.api.submitAnswers(id, entries);
    localStorage.setItem(`results-${id}`, JSON.stringify(results));
    this.props.history.push(`results`);
  }

  async reset() {
    this.setState({ questions: null, results: null });
    const questions = await this.props.api.test(this.props.match.params.id);
    this.setState({ questions });
  }

  render() {
    const { questions } = this.state;
    if (questions) {
      return (
        <Test data={questions} onSubmit={this.submit} busy={this.props.busy} />
      );
    }
    return "Loading...";
  }
}

export default withRouter(withAPI(TestPage));

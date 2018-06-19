import "regenerator-runtime/runtime";
import React from "react";
import ReactDOM from "react-dom";
import { BrowserRouter, Route } from "react-router-dom";
import Menu from "./src/menu";
import Test from "./src/test";
import api from "./src/api";
import withData from "./src/with-data";

const MenuPage = withData(() => api.dicts())(Menu);
const TestPage = withData(props => api.test(props.match.params.id))(Test);

class App extends React.Component {
	render() {
		return (
			<BrowserRouter>
				<div>
					<Route exact path="/" component={MenuPage} />
					<Route path="/:id/test" component={TestPage} />
				</div>
			</BrowserRouter>
		);
	}
}

ReactDOM.render(<App />, document.getElementById("app"));
